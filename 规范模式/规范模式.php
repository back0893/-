<?php
/**
 * 例子
 * 到暖和的地方旅行
 * 现在我想到一个暖和的地方旅游,需要一个温度的查询功能
 */

 /**
  * 用来保存选择的最低温度
  */
 class Travelar{
    public $min_temp;
    public function __construct($temp)
    {
        $this->min_temp=$temp;
    }
 }

 /**
  * 目的地,
  */

  class Destination {
      protected $avg_temps;
      public function __construct($avg_temps)
      {
        $this->avg_temps=$avg_temps;
      }
      public function getAvgTemByMonth($month){
          $key=(int)$month-1;
          return $this->avg_temps[$key]??-1;
      }
  }

  /**
   * Trip类用来组合
   * 在输入 date,traveler,destination后
   * 就可以判断是否有月份合服要求
   */

class Trip{
       public $date;
       public $traveler;
       public $destination;
}


/**
 * 目的地是否足够暖和的标准
 */

 class TripRequiredTemperatureSpecification {
     public function isSatisfiedBy(Trip $trip){
         $trip_temp=$trip->destination->getAvgTemByMonth($trip->date->format('m'));
         return $trip_temp>=$trip->traveler->min_temp;
     }
 }


 $destinations=[
     '成都'=>[1,2,3,4,5,6,7,8,9,10,11,12],
     '广州'=>[1*2,2*2,3*2,4*2,5*2,6*2,7*2,8*2,9*2,10*2,11*2,12*2],
 ];


 $trip=new Trip();
 $trip->date=new DateTime('2019-05-01');
 $trip->traveler=new Travelar(8);
 $trip->destination=new Destination($destinations['成都']);
 $check=new TripRequiredTemperatureSpecification();
 if ($check->isSatisfiedBy($trip)){
     echo "成都合适".PHP_EOL;
 }
 $trip->destination=new Destination($destinations['广州']);
 if ($check->isSatisfiedBy($trip)){
     echo "广州合适".PHP_EOL;
 }


