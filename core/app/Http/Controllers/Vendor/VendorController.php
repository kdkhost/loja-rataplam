<?php

namespace App\Http\Controllers\Vendor;

use App\Helpers\PriceHelper;
use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ChieldCategory;
use App\Models\Item;
use App\Models\Setting;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class VendorController extends Controller
{
    public function __construct()
    {
        $this->middleware('localize');
    }

    public function index(Request $request)
    {
        $attrItemIds = [];
        if ($request->attribute) {
            foreach (Attribute::where('name', $request->attribute)->get() as $attribute) {
                $attrItemIds[] = $attribute->item_id;
            }
        }

        $optionAttributeIds = [];
        if ($request->option) {
            foreach (AttributeOption::whereIn('name', explode(',', $request->option))->get() as $option) {
                $optionAttributeIds[] = $option->attribute_id;
            }
        }

        $optionWiseItemIds = [];
        foreach (Attribute::whereIn('id', $optionAttributeIds)->get() as $attribute) {
            $optionWiseItemIds[] = $attribute->item_id;
        }

        $setting = Setting::first();
        $configuredMaxPrice = (float) ($setting->max_price ?? 0);
        $highestProductPrice = (float) Item::where('status', 1)->max('discount_price');
        $catalogPriceMax = (int) ceil(PriceHelper::setPrice(max($configuredMaxPrice, $highestProductPrice, 1)));

        $sorting = $request->filled('sorting') ? $request->sorting : null;
        $feature = $request->has('quick_filter') && $request->quick_filter == 'feature' ? 1 : null;
        $top = $request->has('quick_filter') && $request->quick_filter == 'top' ? 1 : null;
        $best = $request->has('quick_filter') && $request->quick_filter == 'best' ? 1 : null;
        $new = $request->has('quick_filter') && $request->quick_filter == 'new' ? 1 : null;
        $brand = $request->filled('brand') ? Brand::whereSlug($request->brand)->firstOrFail() : null;
        $search = $request->filled('search') ? $request->search : null;
        $category = $request->filled('category') ? Category::whereSlug($request->category)->firstOrFail() : null;
        $subcategory = $request->filled('subcategory') ? Subcategory::whereSlug($request->subcategory)->firstOrFail() : null;
        $childcategory = $request->filled('childcategory') ? ChieldCategory::where('slug', $request->childcategory)->first() : null;
        $minPrice = $request->filled('minPrice') ? PriceHelper::convertPrice($request->minPrice) : null;
        $maxPrice = $request->filled('maxPrice') ? PriceHelper::convertPrice($request->maxPrice) : null;
        $tag = $request->filled('tag') ? $request->tag : null;

        $items = Item::with('category')
            ->when($category, function ($query, $category) {
                return $query->where('category_id', $category->id);
            })
            ->when($subcategory, function ($query, $subcategory) {
                return $query->where('subcategory_id', $subcategory->id);
            })
            ->when($childcategory, function ($query, $childcategory) {
                return $query->where('childcategory_id', $childcategory->id);
            })
            ->when($feature, function ($query) {
                return $query->whereIsType('feature');
            })
            ->when($tag, function ($query, $tag) {
                return $query->where('tags', 'like', '%' . $tag . '%');
            })
            ->when($top, function ($query) {
                return $query->whereIsType('top');
            })
            ->when($best, function ($query) {
                return $query->whereIsType('best');
            })
            ->when($new, function ($query) {
                return $query->whereIsType('new')->orderBy('id', 'desc');
            })
            ->when($brand, function ($query, $brand) {
                return $query->where('brand_id', $brand->id);
            })
            ->when($search, function ($query, $search) {
                return $query->whereStatus(1)->where('name', 'like', '%' . $search . '%');
            })
            ->when($minPrice, function ($query, $minPrice) {
                return $query->where('discount_price', '>=', $minPrice);
            })
            ->when($maxPrice, function ($query, $maxPrice) {
                return $query->where('discount_price', '<=', $maxPrice);
            })
            ->when($sorting, function ($query, $sorting) {
                return $query->orderBy('discount_price', $sorting == 'low_to_high' ? 'asc' : 'desc');
            })
            ->when($attrItemIds, function ($query, $attrItemIds) {
                return $query->whereIn('id', $attrItemIds);
            })
            ->when($optionWiseItemIds, function ($query, $optionWiseItemIds) {
                return $query->whereIn('id', $optionWiseItemIds);
            })
            ->where('status', 1)
            ->orderBy('id', 'desc')
            ->paginate($setting->view_product);

        $attributeKeywords = [];
        $options = AttributeOption::groupBy('name')->select('attribute_id', 'name', 'id', 'keyword')->get();

        foreach ($options as $option) {
            $attribute = Attribute::withCount('options')->find($option->attribute_id);

            if ($attribute && !in_array($attribute->keyword, $attributeKeywords)) {
                $attributeKeywords[] = $attribute->keyword;
            }
        }

        $attrubutes = [];
        foreach ($attributeKeywords as $keyword) {
            $attribute = Attribute::whereKeyword($keyword)->first();

            if ($attribute) {
                $attrubutes[] = $attribute;
            }
        }

        if ($request->view_check) {
            Session::put('view_catalog', $request->view_check);
        }

        if (!Session::has('view_catalog')) {
            Session::put('view_catalog', 'grid');
        }

        $checkType = Session::get('view_catalog');
        $nameStringCount = $checkType == 'grid' ? 38 : 55;

        return view($request->ajax() ? 'front.catalog.catalog' : 'front.vendor', [
            'attrubutes' => $attrubutes,
            'options' => $options,
            'brand' => $brand,
            'items' => $items,
            'catalogPriceMax' => $catalogPriceMax,
            'name_string_count' => $nameStringCount,
            'category' => $category,
            'subcategory' => $subcategory,
            'childcategory' => $childcategory,
            'checkType' => $checkType,
            'brands' => Brand::withCount('items')->whereStatus(1)->get(),
            'categories' => Category::whereStatus(1)->orderBy('serial', 'asc')->withCount(['items' => function ($query) {
                $query->where('status', 1);
            }])->get(),
        ]);
    }
}
