<?php
namespace Adumskis\LaravelAdvert;


use Adumskis\LaravelAdvert\Model\Advert;
use Adumskis\LaravelAdvert\Model\AdvertCategory;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;

class AdvertManager {

    /**
     * @var array
     */
    private $used = [];

    /**
     * @var object;
     */
    private static $instance;

    /**
     * @return AdvertManager
     */
    public static function getInstance()
    {
        return static::$instance ?: (static::$instance = new self());
    }


    /**
     * Search advert by AdvertCategory type
     * If duplicate set to true then it's possible that advert will be the same with
     * previous showed advert
     *
     * @param $type
     * @param bool $duplicate
     * @return HtmlString|string
     */
    
    /*
    public function getHTML($type, $duplicate = false){
        $advert_category = AdvertCategory::where('type', $type)->first();
        if(!$advert_category){
            return '';
        }

        $advert = $advert_category
            ->adverts()
            ->where('active', true)
            ->where(function($query) use ($duplicate){
                if(!$duplicate){
                    $query->whereNotIn('id', $this->used);
                }
            })
            ->active()
            ->orderBy('viewed_at', 'ASC')
            ->first();

        if($advert){
            $advert->plusViews();
            $advert->updateLastViewed();
            $this->used[$type][] = $advert->id;
            $html = View::make('partials.advert', compact('advert'))->render();
            return new HtmlString($html);
        } else {
            return '';
        }
    }*/
    
    public function getHTML($type, $place, $duplicate = false){
        if(!empty($place) && $place!=$type){
            $advert_category = AdvertCategory::where('type', $type.'_'.$place)->first();
            if(empty($advert_category) || $advert_category->adverts->count()==0){
                $advert_category = AdvertCategory::where('type', $type)->first();//default
            }
        }else{
            $advert_category = AdvertCategory::where('type', $type)->first();
        }

        if(!$advert_category){
            return '';
        }
        \DB::enableQueryLog();
        $advert =Advert::where('advert_category_id',$advert_category->id)
        ->where('active', true)
        ->where(function($query) use ($duplicate){
            if(!$duplicate){
                $query->whereNotIn('id', $this->used);
            }
        })
        ->where(function($q){
            $q->whereNull('featured_from')
                ->orWhere('featured_from','<=',\Carbon\Carbon::now()->toDateTimeString());
        })
        ->where(function($q){
            $q->whereNull('featured_to')
                ->orWhere('featured_to','>=',\Carbon\Carbon::now()->toDateTimeString());
        })
        ->orderBy('viewed_at', 'ASC')
        ->first();
        
        if($advert){
            $advert->plusViews();
            $advert->updateLastViewed();
            $this->used[$type][] = $advert->id;
            $html = View::make('partials.advert', compact('advert'))->render();
            return new HtmlString($html);
        } else {
            return '';
            
        }
    }

}
