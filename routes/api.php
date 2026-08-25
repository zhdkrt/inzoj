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
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Аутентификация пользователей
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Аутентификация ресторанов
Route::post('/restaurant/login', [RestaurantAuthController::class, 'login']);
Route::post('/restaurant/register', [RestaurantAuthController::class, 'register']);

// Аутентификация тренеров
Route::post('/trainer/login', [TrainerAuthController::class, 'login']);
Route::post('/trainer/register', [TrainerAuthController::class, 'register']);

Route::post('/moderator/login', [ModeratorAuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {    
    // ==================== (Авторизованные пользователи) ====================
    // Выход
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Анкета
    Route::get('/questionnaire', [QuestionnaireController::class, 'show']);
    Route::put('/questionnaire/update', [QuestionnaireController::class, 'update']);
    
    // Премиум
    Route::post('/premium/purchase', [PremiumController::class, 'purchase']);
    
    // Профиль
    Route::prefix('profile')->group(function () {
        Route::get('/', function () {
            return response()->json(['message' => 'Profile endpoint']);
        });
        
        Route::get('/user-data', [UserDataController::class, 'show']);
        Route::get('/user-data/edit', [UserDataController::class, 'edit']);
        Route::patch('/user-data/update', [UserDataController::class, 'update']);
        
        Route::get('/targets', [TargetsController::class, 'show']);
        Route::get('/targets/edit', [TargetsController::class, 'edit']);
        Route::patch('/targets/update', [TargetsController::class, 'update']);
        
        Route::get('/ration', [RationController::class, 'show']);
        Route::patch('/ration/update', [RationController::class, 'update']);
    });
    
    // Дневник
    Route::prefix('diary')->group(function () {
        Route::get('/', [DiaryNoteController::class, 'show']);
        Route::get('/item', [DiaryNoteController::class, 'redirection']);
        
        // Приемы пищи
        Route::prefix('meal')->group(function () {
            Route::get('/', [MealController::class, 'show']);
            Route::post('/toggle-favorite', [MealController::class, 'toggleFavorite']);
            
            // Продукты
            Route::prefix('product')->group(function () {
                Route::get('/', [ProductController::class, 'show']);
                Route::post('/add', [ProductController::class, 'addMealProduct']);
                Route::put('/update', [ProductController::class, 'updateMealProduct']);
            });
            
            // Рецепты
            Route::prefix('recepie')->group(function () {
                Route::get('/', [RecepieController::class, 'show']);
                Route::post('/add', [RecepieController::class, 'addMealRecepie']);
                Route::put('/update', [RecepieController::class, 'updateMealRecepie']);
                Route::post('/create', [RecepieController::class, 'createUserRecepie']);
            });
            
            // Фильтры
            Route::get('/filter', [MealController::class, 'showFilter']);
            Route::get('/filter/apply', [MealController::class, 'filterApply']);
        });
        
        // Активность
        Route::prefix('activity')->group(function () {
            Route::get('/', [ActivityController::class, 'show']);
            Route::put('/steps', [ActivityController::class, 'updateSteps']);
            Route::post('/toggle-favorite', [ActivityController::class, 'toggleFavorite']);

            // Тренировки
            Route::prefix('training')->group(function () {
                Route::get('/', [TrainingController::class, 'show']);
                Route::post('/add', [TrainingController::class, 'addTraining']);
                Route::put('/update', [TrainingController::class, 'updateTraining']);
            });
        });
        
        // Вода
        Route::put('/water', [WaterController::class, 'waterCounter']);
    });
    
    // ==================== Премиум маршруты ====================
    Route::middleware('premium')->group(function () {
        
        // Рестораны
        Route::prefix('restaurants')->group(function () {
            Route::get('/', [RestaurantController::class, 'show']);
            Route::get('/menu', [RestaurantController::class, 'restaurantMenu']);
            
            Route::prefix('dish')->group(function () {
                Route::get('/', [DishController::class, 'show']);
                Route::post('/add', [DishController::class, 'addToDiary']);
                Route::delete('/delete', [DishController::class, 'deleteFromDiary']);
            });
        });
        
        // Спорт и тренировки
        Route::prefix('sport')->group(function () {
            Route::get('/filter', [SportController::class, 'filter']);
            Route::get('/trainings', [SportController::class, 'show']);
            Route::post('/signup', [SportController::class, 'signUp']);
            Route::get('/user-trainings', [SportController::class, 'userTrainings']);
            Route::delete('/revoke', [SportController::class, 'revoke']);
            Route::post('/trainer', [SportController::class, 'trainer']);
            Route::get('/trainer/rating', [SportController::class, 'rating']);
        });
    });
    
    // ==================== Маршруты для ресторанов ====================
    Route::prefix('restaurant')->group(function () {
        
        Route::post('/logout', [RestaurantAuthController::class, 'logout']);
        
        Route::prefix('menu')->group(function () {
            Route::get('/', [RestaurantMenuController::class, 'showMenu']);
            Route::get('/dish', [RestaurantMenuController::class, 'showDish']);
            Route::post('/dish/add', [RestaurantMenuController::class, 'dishAdd']);
            Route::put('/dish/update', [RestaurantMenuController::class, 'update']);
            Route::delete('/dish/delete', [RestaurantMenuController::class, 'delete']);
        });
    });
    
    // ==================== Маршруты для тренеров ====================
    Route::prefix('trainer')->group(function () {
        
        Route::post('/logout', [TrainerAuthController::class, 'logout']);
        Route::get('/profile', [TrainerDataController::class, 'profile']);
        Route::get('/change-data', [TrainerDataController::class, 'changeData']);
        Route::put('/update-data', [TrainerDataController::class, 'updateData']);
        
        Route::prefix('trainings')->group(function () {
            Route::get('/', [TrainerTrainingController::class, 'showAllTrainings']);
            Route::get('/new-training', [TrainerTrainingController::class, 'newTraining']);
            Route::get('/training', [TrainerTrainingController::class, 'editTraining']);
            Route::post('/training/create', [TrainerTrainingController::class, 'createTraining']);
            Route::put('/training/update', [TrainerTrainingController::class, 'updateTraining']);
            Route::delete('/training/delete', [TrainerTrainingController::class, 'deleteTraining']);
        });
    });

    Route::middleware('moderator')->prefix('moderator')->group(function () {
        Route::post('/logout', [ModeratorAuthController::class, 'logout']);
        Route::get('/queue', [ModerationController::class, 'queue']);

        Route::get('/recipes', [ModerationController::class, 'recipes']);
        Route::get('/recipes/{id}', [ModerationController::class, 'showRecipe']);
        Route::post('/recipes/{id}', [ModerationController::class, 'reviewRecipe']);

        Route::get('/restaurants', [ModerationController::class, 'restaurants']);
        Route::get('/restaurants/{id}', [ModerationController::class, 'showRestaurant']);
        Route::post('/restaurants/{id}', [ModerationController::class, 'reviewRestaurant']);

        Route::get('/trainers', [ModerationController::class, 'trainers']);
        Route::get('/trainers/{id}', [ModerationController::class, 'showTrainer']);
        Route::post('/trainers/{id}', [ModerationController::class, 'reviewTrainer']);
    });
});

