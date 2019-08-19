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
    public function getHTML($type, $place, $duplicate = false){
        if(!empty($place) && $place!=$type){
            $advert_category = AdvertCategory::where('type', $type.'_'.$place)->first();            
        }
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
            ->where(function($q){
                $q->where('featured_from','>=',\Carbon\Carbon::now()->toDateTimeString();
            })
            ->where(function($q){
                $q->where('featured_to','<=',\Carbon\Carbon::now()->toDateTimeString();
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
    }

}
