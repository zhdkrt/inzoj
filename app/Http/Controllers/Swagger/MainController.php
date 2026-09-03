<?php

namespace App\Http\Controllers\Swagger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *     title="Your API Title",
 *     version="1.0.0",
 *     description="API documentation for your Laravel application",
 *     termsOfService="https://your-website.com/terms",
 *     @OA\Contact(
 *         email="admin@example.com"
 *     ),
 *     @OA\License(
 *         name="Apache 2.0",
 *         url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="/",
 *     description="Current host"
 * )
 * 
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Configured host"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="userSanctumToken",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="sanctum",
 *     description="Enter user sanctum token (from Auth)"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="trainerSanctumToken",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="sanctum",
 *     description="Enter trainer sanctum token (from Trainer Auth)"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="restaurantSanctumToken",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="sanctum",
 *     description="Enter restaurant sanctum token (from Restaurant Auth)"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="moderatorSanctumToken",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="sanctum",
 *     description="Enter moderator sanctum token (from Moderator Auth)"
 * )
 * 
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="User",
 * 
 *     @OA\Property(property="id", type="integer", format="int64", example=1, description="User ID"),
 *     @OA\Property(property="name", type="string", example="Дима", description="Full name", nullable=true),
 *     @OA\Property(property="email", type="string", format="email", example="example@gmail.com", description="Email address"),
 *     @OA\Property(property="goal", type="string", enum={"lose_weight", "gain_muscle", "maintain", "other"}, nullable=true, description="Fitness goal"),
 *     @OA\Property(property="current_weight", type="number", format="float", example=75, nullable=true, description="Current weight in kg"),
 *     @OA\Property(property="target_weight", type="number", format="float", example=70, nullable=true, description="Target weight in kg"),
 *     @OA\Property(property="height", type="integer", example=175, nullable=true, description="Height in cm"),
 *     @OA\Property(property="age", type="integer", example=30, nullable=true, description="Age in years"),
 *     @OA\Property(property="gender", type="string", enum={"male", "female"}, nullable=true, description="Gender"),
 *     @OA\Property(property="activity_level", type="string", enum={"low", "medium", "high", "expert"}, nullable=true, description="Physical activity level"),
 *     @OA\Property(property="calories", type="integer", example=2000, nullable=true, description="Daily calorie goal"),
 *     @OA\Property(property="proteins", type="number", format="float", example=135.0, nullable=true, description="Daily protein goal in grams"),
 *     @OA\Property(property="fats", type="number", format="float", example=60.0, nullable=true, description="Daily fat goal in grams"),
 *     @OA\Property(property="carbs", type="number", format="float", example=200.0, nullable=true, description="Daily carb goal in grams"),
 *     @OA\Property(property="water", type="number", format="float", example=2.0, nullable=true, description="Daily water goal in liters"),
 *     @OA\Property(property="steps", type="integer", example=10000, nullable=true, description="Daily step goal"),
 *     @OA\Property(property="food_preferences", type="string", enum={"no_preferences", "vegetarian", "vegan"}, nullable=true, example="vegetarian", description="Dietary preferences"),
 *     @OA\Property(property="allergies", type="string", nullable=true, description="Pipe-separated list of allergies", example="lactose|nuts"),
 *     @OA\Property(property="is_premium", type="boolean", example=false, description="Whether user has premium subscription"),
 *     @OA\Property(property="premium_until", type="string", format="date-time", nullable=true, description="Premium subscription expiration date"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp")
 * )
 * 
 * @OA\Schema(
 *     schema="Trainer",
 *     type="object",
 *     title="Trainer",
 *     description="Trainer model",
 *     
 *     @OA\Property(property="id", type="integer", format="int64", example=1, description="Trainer ID"),
 *     @OA\Property(property="email", type="string", format="email", example="trainer@gmail.com", description="Email address"),
 *     @OA\Property(property="name", type="string", example="John", description="First name", nullable=true),
 *     @OA\Property(property="surname", type="string", example="Doe", description="Last name", nullable=true),
 *     @OA\Property(property="birthday", type="string", format="date", example="1990-01-15", description="Birth date", nullable=true),
 *     @OA\Property(property="experience", type="integer", example=5, description="Years of experience", minimum=0, nullable=true),
 *     @OA\Property(property="achievements", type="string", example="Certified personal trainer, Nutrition specialist", description="Achievements", nullable=true),
 *     @OA\Property(property="rating", type="number", format="float", example=4.8, description="Average rating", minimum=0, maximum=5, nullable=true),
 *     @OA\Property(property="rating_count", type="integer", example=42, description="Number of ratings", minimum=0, nullable=true),
 *     @OA\Property(property="moderation_status", type="string", enum={"pending", "approved", "rejected"}, example="approved"),
 *     @OA\Property(property="moderated_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="moderation_comment", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp")
 * )
 * 
 * @OA\Schema(
 *     schema="TrainingCategory",
 *     type="object",
 *     title="Training Category",
 *     description="Training category model",
 *     
 *     @OA\Property(property="id", type="integer", format="int64", example=1, description="Category ID"),
 *     @OA\Property(property="name", type="string", example="Yoga", description="Category name"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp")
 * )
 * 
 * @OA\Schema(
 *     schema="Training",
 *     type="object",
 *     title="Training",
 *     description="Training model",
 *     
 *     @OA\Property(property="id", type="integer", format="int64", example=1, description="Training ID"),
 *     @OA\Property(property="trainer_user_id", type="integer", example=1, description="Trainer ID who created this training"),
 *     @OA\Property(property="name", type="string", example="Morning Yoga", description="Training name"),
 *     @OA\Property(property="time_amount", type="integer", example=60, description="Duration in minutes"),
 *     @OA\Property(property="description", type="string", example="Relaxing morning yoga session", description="Training description", nullable=true),
 *     @OA\Property(property="price", type="number", format="float", example=500, description="Price in rubles"),
 *     @OA\Property(property="start_time", type="string", format="time", example="09:00:00", description="Start time of the training"),
 *     @OA\Property(property="date", type="string", format="date", example="2025-12-25", description="Date of the training"),
 *     @OA\Property(property="category_id", type="integer", example=1, description="Category ID"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp")
 * )
 * 
 * 
 * 
 * @OA\Schema(
 *     schema="Restaurant",
 *     type="object",
 *     title="Restaurant",
 *     description="Restaurant model",
 *     
 *     @OA\Property(property="id", type="integer", format="int64", example=1, description="Restaurant ID"),
 *     @OA\Property(property="name", type="string", example="Best Restaurant", description="Restaurant name"),
 *     @OA\Property(property="restaurant_type", type="string", enum={"cafe", "canteen", "fine_restaurant", "fast_food", "bistro", "pub", "bar", "other"}, description="Type of restaurant", nullable=true),
 *     @OA\Property(property="moderation_status", type="string", enum={"pending", "approved", "rejected"}, example="approved"),
 *     @OA\Property(property="moderated_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="moderation_comment", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp")
 * )
 * 
 * @OA\Schema(
 *     schema="Dish",
 *     type="object",
 *     title="Dish",
 * 
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="restaurant_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Salat"),
 *     @OA\Property(property="price", type="integer", example=42),
 *     @OA\Property(property="weight", type="integer", example=250),
 *     @OA\Property(property="ingredients", type="string", nullable=true),
 *     @OA\Property(property="proteins", type="number", format="float", nullable=true),
 *     @OA\Property(property="fats", type="number", format="float", nullable=true),
 *     @OA\Property(property="carbs", type="number", format="float", nullable=true),
 *     @OA\Property(property="calories", type="number", format="float", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * 
 * 
 * @OA\Schema(
 *     schema="DiaryNote",
 *     type="object",
 *     title="Diary Note",
 *     
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="diary_date", type="string", format="date-time", example="2025-04-01 00:00:00"),
 *     @OA\Property(property="current_calories", type="number", format="float", example=1500.5),
 *     @OA\Property(property="burned_calories", type="number", format="float", example=300.0),
 *     @OA\Property(property="current_proteins", type="number", format="float", example=75.5),
 *     @OA\Property(property="current_fats", type="number", format="float", example=45.2),
 *     @OA\Property(property="current_carbs", type="number", format="float", example=120.8),
 *     @OA\Property(property="current_water", type="number", format="float", example=1.50),
 *     @OA\Property(property="current_steps", type="integer", example=8000),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 *  @OA\Schema(
 *     schema="UserMeal",
 *     type="object",
 *     title="User Meal",
 *     description="User's meal record linking diary with product/recepie/dish",
 *     
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="diary_note_id", type="integer", example=123),
 *     @OA\Property(property="product_id", type="integer", nullable=true, example=5),
 *     @OA\Property(property="recepie_id", type="integer", nullable=true, example=null),
 *     @OA\Property(property="dish_id", type="integer", nullable=true, example=null),
 *     @OA\Property(property="meal_type", type="string", enum={"breakfast", "lunch", "dinner", "snack"}, example="breakfast"),
 *     @OA\Property(property="amount", type="integer", example=100, description="Amount in grams"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     title="Product",
 *     
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="name", type="string", example="Apple"),
 *     @OA\Property(property="calories", type="number", format="float", example=52.0),
 *     @OA\Property(property="proteins", type="number", format="float", example=0.3),
 *     @OA\Property(property="fats", type="number", format="float", example=0.2),
 *     @OA\Property(property="carbs", type="number", format="float", example=14.0),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *     schema="Recepie",
 *     type="object",
 *     title="Recepie",
 *     
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="name", type="string", example="Caesar Salad"),
 *     @OA\Property(property="instructions", type="string", example="Mix ingredients..."),
 *     @OA\Property(property="image", type="string", example="/images/recepies/caesar.jpg", nullable=true),
 *     @OA\Property(property="calories", type="number", format="float", example=450.5),
 *     @OA\Property(property="proteins", type="number", format="float", example=25.0),
 *     @OA\Property(property="fats", type="number", format="float", example=30.0),
 *     @OA\Property(property="carbs", type="number", format="float", example=15.0),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *     schema="RecepieIngredient",
 *     type="object",
 *     title="Recepie Ingredient",
 * 
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="recepie_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Тыква"),
 *     @OA\Property(property="amount", type="string", example="1,5 кг"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *     schema="UserRecepie",
 *     type="object",
 *     title="User Recipe",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Oatmeal"),
 *     @OA\Property(property="instructions", type="string", example="Mix oats with water"),
 *     @OA\Property(property="calories", type="number", format="float", example=150.0),
 *     @OA\Property(property="proteins", type="number", format="float", example=5.0),
 *     @OA\Property(property="fats", type="number", format="float", example=3.0),
 *     @OA\Property(property="carbs", type="number", format="float", example=27.0),
 *     @OA\Property(property="moderation_status", type="string", enum={"pending", "approved", "rejected"}, example="pending"),
 *     @OA\Property(property="moderated_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="moderation_comment", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *     schema="Activity",
 *     type="object",
 *     title="Activity",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Отжимания"),
 *     @OA\Property(property="time", type="string", example="10 мин"),
 *     @OA\Property(property="calories", type="integer", example=80),
 *     @OA\Property(property="category", type="string", nullable=true, example="chest"),
 *     @OA\Property(property="location_type", type="string", enum={"home", "gym", "outdoor", "any"}, example="home"),
 *     @OA\Property(property="is_custom", type="boolean", example=false),
 *     @OA\Property(property="is_premium", type="boolean", example=false),
 *     @OA\Property(property="has_video", type="boolean", example=true),
 *     @OA\Property(property="video_url", type="string", nullable=true, example="https://example.com/video.mp4"),
 *     @OA\Property(property="video_locked", type="boolean", example=false, description="True when a video exists but the user is not premium"),
 *     @OA\Property(property="is_favorite", type="boolean", example=false)
 * )
 */

class MainController extends Controller
{
    //
}
