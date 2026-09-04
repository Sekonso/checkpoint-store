<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Product::class, 'name')->ignore($this->route('product'))
            ],

            'description' => [
                'required',
                'string',
                'max:5000'
            ],

            'category_id' => [
                'required',
                'integer',
                Rule::exists(ProductCategory::class, 'id')
            ],

            'price' => [
                'required',
                'integer',
                'min:1000',
            ],

            'stock' => [
                'required',
                'min:0',
                'integer'
            ],

            'in_display' => [
                'required',
                'boolean'
            ],

            'image-1' => [
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:2048',
            ],

            'image-2' => [
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:2048',
            ],

            'image-3' => [
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:2048',
            ],
        ];
    }
}
