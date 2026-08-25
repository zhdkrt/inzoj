<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\QuestionnaireController;
use App\Http\Controllers\PremiumController;

use App\Http\Controllers\Profile\UserDataController;
use App\Http\Controllers\Profile\TargetsController;
use App\Http\Controllers\Profile\RationController;

use App\Http\Controllers\Diary\DiaryNoteController;
use App\Http\Controllers\Diary\MealController;
use App\Http\Controllers\Diary\ProductController;
use App\Http\Controllers\Diary\RecepieController;
use App\Http\Controllers\Diary\ActivityController;
use App\Http\Controllers\Diary\TrainingController;
use App\Http\Controllers\Diary\WaterController;

use App\Http\Controllers\Restaurant\RestaurantController;
use App\Http\Controllers\Restaurant\DishController;

use App\Http\Controllers\Sport\SportController;

use App\Http\Controllers\RestaurantUser\RestaurantAuthController;
use App\Http\Controllers\RestaurantUser\RestaurantMenuController;

use App\Http\Controllers\TrainerUser\TrainerAuthController;
use App\Http\Controllers\TrainerUser\TrainerDataController;
use App\Http\Controllers\TrainerUser\TrainerTrainingController;

use App\Http\Controllers\Moderator\ModeratorAuthController;
use App\Http\Controllers\Moderator\ModerationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/csrf-token', function () {
    return response()->json([
        'csrf_token' => csrf_token(),
    ]);
});


Route::get('/', function () {
    return redirect()->route('user.login');
});
Route::get('/register', function () {
    return view('auth.register');
})->name('user.register');
Route::get('/login', function () {
    return view('auth.login');
})->name('user.login');
Route::post('/register', [AuthController::class, 'register'])->name('user.register.form');
Route::post('/login', [AuthController::class, 'login'])->name('user.login.form');


Route::middleware('auth:web')->group(function () {
    Route::get('/questionnaire', [QuestionnaireController::class, 'show'])->name('questionnaire');
    Route::post('/questionnaire/update', [QuestionnaireController::class, 'update'])->name('questionnaire.update');
    Route::get('/premium', [PremiumController::class, 'show'])->name('premium');
    Route::post('/premium/purchase', [PremiumController::class, 'purchase'])->name('premium.purchase');

    Route::get('/profile', function () {
        return view('profile.index');
    })->name('profile');
    Route::post('/profile/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile/user-data', [UserDataController::class, 'show'])->name('userData');
    Route::get('/profile/user-data/edit', [UserDataController::class, 'edit'])->name('userData.edit');
    Route::post('/profile/user-data/update', [UserDataController::class, 'update'])->name('userData.update');
    Route::get('/profile/targets', [TargetsController::class, 'show'])->name('targets');
    Route::get('/profile/targets/edit', [TargetsController::class, 'edit'])->name('targets.edit');
    Route::post('/profile/targets/update', [TargetsController::class, 'update'])->name('targets.update');
    Route::get('/profile/ration', [RationController::class, 'show'])->name('ration');
    Route::post('/profile/ration/update', [RationController::class, 'update'])->name('ration.update');

    Route::get('/diary', [DiaryNoteController::class, 'show'])->name('diary');
    Route::get('/diary/item', [DiaryNoteController::class, 'redirection'])->name('diary.item');
    Route::get('/diary/meal', [MealController::class, 'show'])->name('meal');
    Route::post('/diary/meal/toggle-favorite', [MealController::class, 'toggleFavorite'])->name('meal.toggleFavorite');
    Route::get('/diary/meal/product', [ProductController::class, 'show'])->name('product');
    Route::post('/diary/meal/product/add', [ProductController::class, 'addMealProduct'])->name('product.add');
    Route::post('/diary/meal/product/update', [ProductController::class, 'updateMealProduct'])->name('product.update');
    Route::get('/diary/meal/recepie', [RecepieController::class, 'show'])->name('recepie');
    Route::post('/diary/meal/recepie/add', [RecepieController::class, 'addMealRecepie'])->name('recepie.add');
    Route::post('/diary/meal/recepie/update', [RecepieController::class, 'updateMealRecepie'])->name('recepie.update');
    Route::get('/diary/meal/recepie/filter', [MealController::class, 'showFilter'])->name('filter');
    Route::get('/diary/meal/recepie/filter/apply', [MealController::class, 'filterApply'])->name('filter.apply');
    Route::get('/diary/meal/recepie/userRecepieForm', [RecepieController::class, 'showUserRecepieForm'])->name('recepie.userRecepie');
    Route::get('/diary/meal/recepie/userRecepieForm/create', [RecepieController::class, 'createUserRecepie'])->name('recepie.createUserRecepie');
    Route::get('/diary/activity', [ActivityController::class, 'show'])->name('activity');
    Route::post('/diary/activity/steps', [ActivityController::class, 'updateSteps'])->name('activity.steps.update');
    Route::post('/diary/activity/toggle-favorite', [ActivityController::class, 'toggleFavorite'])->name('activity.toggleFavorite');
    Route::get('/diary/activity/training', [TrainingController::class, 'show'])->name('training');
    Route::post('/diary/activity/trining/add', [TrainingController::class, 'addTraining'])->name('training.add');
    Route::post('/diary/activity/trining/update', [TrainingController::class, 'updateTraining'])->name('training.update');
    Route::post('/diary/water', [WaterController::class, 'waterCounter'])->name('water');

    Route::middleware('premium')->group(function () {
        Route::get('/restaurants', [RestaurantController::class, 'show'])->name('restaurant');
        Route::get('/restaurants/menu', [RestaurantController::class, 'restaurantMenu'])->name('restaurant.menu');
        Route::get('/restaurants/dish', [DishController::class, 'show'])->name('dish');
        Route::post('/restaurants/dish/add', [DishController::class, 'addToDiary'])->name('dish.add');
        Route::post('/restaurants/dish/delete', [DishController::class, 'deleteFromDiary'])->name('dish.delete');
    
        Route::get('/filter-trainings', [SportController::class, 'filter'])->name('sport');  
        Route::get('/trainings', [SportController::class, 'show'])->name('trainings');  
        Route::post('/signup-training', [SportController::class, 'signUp'])->name('signUpTraining');  
        Route::get('/user-trainings', [SportController::class, 'userTrainings'])->name('userTrainings');  
        Route::post('/revoke-training', [SportController::class, 'revoke'])->name('revoke');  
        Route::post('/trainer', [SportController::class, 'trainer'])->name('trainer');  
        Route::get('/trainer/rating', [SportController::class, 'rating'])->name('rating');  
    });
});


Route::prefix('restaurant-user')->name('restaurantUser.')->group(function () {
    Route::get('/login', function () {
        return view('restaurantUser.login');
    })->name('login');
    Route::post('/login-check', [RestaurantAuthController::class, 'login'])->name('loginCheck');
    
    Route::middleware('auth:restaurant')->group(function () {
        Route::post('/restaurant/menu/logout', [RestaurantAuthController::class, 'logout'])->name('logout');

        Route::get('restaurant/menu', [RestaurantMenuController::class, 'showMenu'])->name('menu');
        Route::get('/restaurant/menu/new-dish', function () {
            return view('restaurantUser.newDish');
        })->name('newDish');
        Route::post('/restaurant/menu/add', [RestaurantMenuController::class, 'dishAdd'])->name('dish.add');
        Route::get('/restaurant/menu/dish', [RestaurantMenuController::class, 'showDish'])->name('dish');
        Route::post('/restaurant/menu/dish/update', [RestaurantMenuController::class, 'updateDish'])->name('updateDish');
        Route::get('/restaurant/menu/dish/delete', [RestaurantMenuController::class, 'deleteDish'])->name('deleteDish');
    });
});


Route::prefix('trainer-user')->name('trainerUser.')->group(function () {
    Route::get('/login', function () {
        return view('trainerUser.login');
    })->name('login');
    Route::get('/register', function () {
        return view('trainerUser.register');
    })->name('register');
    Route::post('/login-check', [TrainerAuthController::class, 'login'])->name('loginCheck');
    Route::post('/register-check', [TrainerAuthController::class, 'register'])->name('registerCheck');
    
    Route::middleware('auth:trainer')->group(function () {
        Route::post('/logout', [TrainerAuthController::class, 'logout'])->name('logout');
        Route::get('/profile', [TrainerDataController::class, 'profile'])->name('profile');
        Route::get('/change-data', [TrainerDataController::class, 'changeData'])->name('changeData');
        Route::post('/update-data', [TrainerDataController::class, 'updateData'])->name('updateData');
        Route::get('/all-trainings', [TrainerTrainingController::class, 'showAllTrainings'])->name('showAllTrainings');
        Route::get('/new-training', [TrainerTrainingController::class, 'newTraining'])->name('newTraining');
        Route::post('/training-action', [TrainerTrainingController::class, 'trainingAction'])->name('trainingAction');
        Route::get('/training', [TrainerTrainingController::class, 'editTraining'])->name('editTraining');
    });
});

Route::prefix('moderator')->name('moderator.')->group(function () {
    Route::get('/login', function () {
        if (auth('moderator')->check()) {
            return redirect()->route('moderator.queue');
        }
        return view('moderator.login');
    })->name('login');
    Route::post('/login', [ModeratorAuthController::class, 'login'])->name('loginCheck');

    Route::middleware('auth:moderator')->group(function () {
        Route::post('/logout', [ModeratorAuthController::class, 'logout'])->name('logout');
        Route::get('/', [ModerationController::class, 'queue'])->name('queue');

        Route::get('/recipes', [ModerationController::class, 'recipes'])->name('recipes');
        Route::get('/recipes/{id}', [ModerationController::class, 'showRecipe'])->name('recipes.show');
        Route::post('/recipes/{id}', [ModerationController::class, 'reviewRecipe'])->name('recipes.review');

        Route::get('/restaurants', [ModerationController::class, 'restaurants'])->name('restaurants');
        Route::get('/restaurants/{id}', [ModerationController::class, 'showRestaurant'])->name('restaurants.show');
        Route::post('/restaurants/{id}', [ModerationController::class, 'reviewRestaurant'])->name('restaurants.review');

        Route::get('/trainers', [ModerationController::class, 'trainers'])->name('trainers');
        Route::get('/trainers/{id}', [ModerationController::class, 'showTrainer'])->name('trainers.show');
        Route::post('/trainers/{id}', [ModerationController::class, 'reviewTrainer'])->name('trainers.review');
    });
});