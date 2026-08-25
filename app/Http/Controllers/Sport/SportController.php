<?php

namespace App\Http\Controllers\Sport;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Training; 
use App\Models\TrainingCategory; 
use App\Models\TrainerUser;
use App\Models\UserTraining;

class SportController extends Controller
{
    public function filter(Request $request) {    
        $trainingCategories = TrainingCategory::pluck('name', 'id');
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'categories' => $trainingCategories
            ]);
        }
        return view('sport.filters', ['trainingCategories' => $trainingCategories]);
    }
        
    public function show(Request $request) {
        $trainingCategories = TrainingCategory::pluck('name', 'id');

        $suitableTrainings = [];
        $categoriesFilter = $request->get('categories', []);
        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        if ($request->expectsJson() && (!$fromDate || !$toDate)) {
            return response()->json([
                'success' => false,
                'message' => 'from_date and to_date are required'
            ], 422);
        }        

        $query = Training::whereBetween('date', [$fromDate, $toDate])
            ->whereDoesntHave('userTraining')
            ->whereHas('trainerUser', function ($trainerQuery) {
                $trainerQuery->approved();
            })
            ->orderBy('date', 'asc');

        if (!in_array('all', $categoriesFilter) && !empty($categoriesFilter)) {
            $query->whereIn('category_id', $categoriesFilter);
        }

        $suitableTrainings = $query->limit(16)->get();
        $uniqueTrainersId = $suitableTrainings->pluck('trainer_user_id')->unique()->all();
        $trainers = [];
        foreach ($uniqueTrainersId as $id) {
            $trainer = TrainerUser::find($id);
            $trainerName = $trainer->name . ' ' . $trainer->surname;
            $trainers[$id] = $trainerName;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'trainings' => $suitableTrainings,
                'categories' => $trainingCategories,
                'trainers' => $trainers,
                'filters' => [
                    'categories' => $categoriesFilter,
                    'from_date' => $fromDate,
                    'to_date' => $toDate
                ]
            ]);
        }        

        return view('sport.index', [
            'trainings' => $suitableTrainings,
            'categories' => $trainingCategories,
            'trainers' => $trainers,
        ]);
    }

    public function signUp(Request $request) {
        $userId = Auth::user()->id;
        $trainingId = $request->training_id;

        $existing = UserTraining::where('user_id', $userId)
            ->where('training_id', $trainingId)
            ->first();
            
        if ($existing) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Already signed up for this training'
                ], 400);
            }
            return redirect()->back()->with('error', 'Вы уже записаны на эту тренировку');
        }

        $training = Training::with('trainerUser')->find($trainingId);
        if (!$training || !$training->trainerUser || !$training->trainerUser->isApproved()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Training is not available'
                ], 404);
            }
            return redirect()->back()->with('error', 'Тренировка недоступна');
        }

        UserTraining::create([
            'user_id' => $userId,
            'training_id' => $trainingId,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Successfully signed up for training',
                'training_id' => $trainingId
            ]);
        }

        return redirect()->route('userTrainings');
    }

    public function userTrainings(Request $request) {
        $trainingCategories = TrainingCategory::pluck('name', 'id');

        $userId = Auth::user()->id;
        $trainersId = UserTraining::where('user_id', $userId)->pluck('training_id')->toArray();
        $allUserTrainings = Training::whereIn('id', $trainersId)
            ->orderBy('date', 'asc')
            ->get();

        $today = now()->startOfDay()->format('Y-m-d');
        $userTrainings = $allUserTrainings->where('date', '>=', $today);
        $oldTrainings = $allUserTrainings->where('date', '<', $today);
        
        $trainersId = $allUserTrainings->pluck('trainer_user_id')->unique()->toArray();
        $trainers = [];
        foreach ($trainersId as $id) {
            $trainer = TrainerUser::find($id);
            $trainerName = $trainer->name . ' ' . $trainer->surname;
            $trainers[$id] = $trainerName;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'user_trainings' => $userTrainings->values(),
                'old_trainings' => $oldTrainings->values(),
                'categories' => $trainingCategories,
                'trainers' => $trainers
            ]);
        }        

        return view('sport.userTrainings', [
            'userTrainings' => $userTrainings,
            'oldTrainings' => $oldTrainings,
            'categories' => $trainingCategories,
            'trainers' => $trainers,
        ]);
    }

    public function revoke(Request $request) {
        $userId = Auth::user()->id;
        $trainingId = $request->training_id;

        $deleted = UserTraining::where('user_id', $userId)
            ->where('training_id', $trainingId)
            ->delete();

        if ($deleted == 0) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Training signup not found'
                ], 404);
            }
            return redirect()->back()->with('error', 'Запись на тренировку не найдена');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Successfully unsubscribed from training',
                'training_id' => $trainingId
            ]);
        }

        return redirect()->route('userTrainings');
    }

    public function trainer(Request $request) {
        $trainerId = $request->trainer_id;
        $trainer = TrainerUser::approved()->find($trainerId);

        if (!$trainer) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Trainer not found'
                ], 404);
            }
            abort(404, 'Trainer not found');
        }        

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'trainer' => $trainer,
            ]);
        }

        $previousUrl = url()->previous();

        return view('sport.trainer', [
            'trainer' => $trainer,
            'url' => $previousUrl,
        ]);
    }

    public function rating(Request $request) {
        $trainerId = $request->trainer_id;
        $trainer = TrainerUser::approved()->find($trainerId);

        if (!$trainer) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Trainer not found'
                ], 404);
            }
            abort(404, 'Trainer not found');
        }        

        $userRating = $request->rating;
        $oldRating = $trainer->rating ?? 0;
        
        if ($oldRating == 0) {
            $trainer->update([
                'rating' => $userRating,
                'rating_count' => 1,
            ]);
        } else {
            $newRating = $userRating + $oldRating;
            $trainer->update([
                'rating' => $newRating,
                'rating_count' => $trainer->rating_count + 1,
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Rating submitted successfully',
                'trainer_id' => $trainerId,
                'new_rating' => $trainer->rating,
                'rating_count' => $trainer->rating_count
            ]);
        }

        $url = $request->url;

        return view('sport.trainer', [
            'trainer' => $trainer,
            'url' => $url,
        ]);
    }
}
