<?php

namespace App\Services;

use App\Models\Favorite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FavoriteService
{
    protected Favorite $model;

    public function __construct(Favorite $model)
    {
        $this->model = $model;
    }

    /**
     * Get all favorites for the authenticated user
     */
    public function indexFavorites($perPage = 10)
    {
        return $this->model->byUser(Auth::id())->with('product')->paginate($perPage);
    }

    /**
     * Add a product to favorites
     */
    public function storeFavorite(array $data)
    {
        try {
            $favorite = $this->model->firstOrCreate([
                'user_id' => Auth::id(),
                'product_id' => $data['product_id']
            ]);
            return [
                'status' => true,
                'message' => 'Product added to favorites successfully',
                'data' => $favorite
            ];
        } catch (\Exception $e) {
            Log::error('Favorite creation failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'An error occurred while adding to favorites'
            ];
        }
    }

    /**
     * Remove a product from favorites
     */
    public function destroyFavorite($favoriteId)
    {
        try {
            $favorite = $this->model->find($favoriteId);
            if (!$favorite) {
                return [
                    'status' => false,
                    'message' => 'Favorite not found'
                ];
            }
            $favorite->delete();
            return [
                'status' => true,
                'message' => 'Favorite removed successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Favorite deletion failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'An error occurred while removing from favorites'
            ];
        }
    }

    /**
     * Get a specific favorite by ID
     */
    public function showFavorite($favoriteId)
    {
        return $this->model->with('product')->find($favoriteId);
    }

    /**
     * Update a favorite
     */
    public function updateFavorite(array $data, $favoriteId)
    {
        try {
            $favorite = $this->model->find($favoriteId);
            if (!$favorite) {
                return [
                    'status' => false,
                    'message' => 'Favorite not found'
                ];
            }
            $favorite->update([
                'user_id' => $data['user_id'] ?? $favorite->user_id,
                'product_id' => $data['product_id'] ?? $favorite->product_id
            ]);
            return [
                'status' => true,
                'message' => 'Favorite updated successfully',
                'data' => $favorite
            ];
        } catch (\Exception $e) {
            Log::error('Favorite update failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'An error occurred while updating favorite'
            ];
        }
    }
}