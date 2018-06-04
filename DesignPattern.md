# 设计模式

## 1.1 单例模式

> 它的核心结构中只包含一个被称为单例的特殊类。

> 通过单例模式可以保证系统中一个类只有一个实例。

> 即一个类只有一个对象实例。

核心要点

 一个类只能有一个对象；
 必须是自行创建这个类的对象；
 要向整个系统提供这一个对象；

具体实现

 单例模式的类只提供私有的构造函数；

 类定义中含有一个该类的静态私有对象；
 该类提供了一个静态的公有的函数用于创建或获取它本身的静态私有对象；
 有一个private的clone方法，防止克隆；

优点	缺点
一、实例控制：单例模式会阻止其他对象实例化其自己的单例对象的副本，从而确保所有对象都访问唯一实例。开销：虽然数量很少，但如果每次对象请求引用时都要检查是否存在类的实例，将仍然需要一些开销。
二、灵活性：因为类控制了实例化过程，所以类可以灵活更改实例化过程。	可能的开发混淆：使用单例对象（尤其在类库中定义的对象）时，开发人员必须记住自己不能使用new关键字实例化对象。
三、对象生存期：不能解决删除单个对象的问题。



```Php
class Uni{

      //创建静态私有的变量保存该类对象

      static private $instance;

      //参数

      private $config;

      //防止直接创建对象

      private function __construct($config){

         this -> config = config;

                 echo "我被实例化了";

     }

     //防止克隆对象

     private function __clone(){

}
 static public function getInstance($config){
   	//判断$instance是否是Uni的对象
    //没有则创建
     if (!self::$instance instanceof self) {
         self::$instance = new self($config);
     }
     return self::$instance;
 }
 public function getName(){
     echo $this -> config;
 }
}

 $db1 = Uni::getInstance(1);

 $db1 -> getName();

 echo "<br>";

 $db2 = Uni::getInstance(4);

 $db2 -> getName();

我被实例化了1

1 # 没有再次实例化，输出的是第一次实例化

```


## 1.2 工厂模式

> 工厂模式是一种类，它具有为您创建对象的某些方法。

> 您可以使用工厂类创建对象，而不直接使用 new。

> 这样，如果您想要更改所创建的对象类型，只需更改该工厂即可。

> 与此同时，使用该工厂的所有代码会自动更改。

划分	区别	应用范围

简单工厂	用来生产同一等级结构中的任意产品。（对于增加新的产品，无能为力。）	工厂类负责创建的对象较少，客户只知道传入工厂类的参数，对于如何创建对象不关心。

工厂方法	用来生产同一等级结构中的固定产品。（支持增加任意产品）	当一个类不知道它所必须创建对象的类或一个类希望由子类来指定它所创建的对象时，当类将创建对象的职责委托给多个帮助子类中得某一个，并且你希望将哪一个帮助子类是代理者这一信息局部化的时候，可以使用工厂方法模式。

抽象工厂	用来生产不同产品族的全部产品。（对于增加新的产品，无能为力；支持增加产品族）	一个系统不应当依赖于产品类实例何如被创建，组合和表达的细节，这对于所有形态的工厂模式都是重要的。这个系统有多于一个的产品族，而系统只消费其 中某一产品族。同属于同一个产品族的产品是在一起使用的，这一约束必须在系统的设计中体现出来。系统提供一个产品类的库，所有的产品以同样的接口出现，从 而使客户端不依赖于实现。

### 1.2.1 简单工厂

```php
<?php

简单工厂又叫静态工厂方法模式，这样理解可以确定，简单工厂模式是通过一个静态方法创建对象的。

interface people

{

	function marry();

}

class man implements people{

	function marry()

	{

		echo '送玫瑰、送钻戒!<br/>';

	}

}

class women implements people{

	function marry()

	{

		echo '穿婚纱，戴钻戒！<br/>';

	}

}

/**

*/
  class simpleFactory
  {
  static function createMan(){
  	return new man;
  }
  static function createWomen(){
  	return new women;
  }
  }

$man = simpleFactory::createMan();

$man->marry();

$women = simpleFactory::createWomen();

$women->marry();

运行结果

送玫瑰、送钻戒!

穿婚纱，戴钻戒！

```



### 1.2.2 工厂方法

```php
<?php

定义一个创建对象的接口，让子类决定哪个类实例化。 他可以解决简单工厂模式中的封闭开放原则问题。

interface people

{

	function marry();

}

class man implements people{

	function marry()

	{

		echo '送玫瑰、送钻戒!<br/>';

	}

}

class women implements people{

	function marry()

	{

		echo '穿婚纱，戴钻戒！<br/>';

	}

}

interface createMan { // 注意了，这里是简单工厂本质区别所在，将对象的创建抽象成一个接口。

	function create();

}

class FactoryMan implements createMan{

	function create() {

		return new man;

	}

}

class FactoryWomen implements createMan {

	function create() {

		return new women;

	}

}

class Client {

// 简单工厂里的静态方法

	function test() {

		$Factory = new FactoryMan;

		man = Factory->create();

		$man->marry();

		$Factory = new FactoryWomen;

		man = Factory->create();

		$man->marry();

	}

}

$f = new Client;

$f->test(); 

运行结果

送玫瑰、送钻戒!

穿婚纱，戴钻戒！

```

### 1.2.3 抽象工厂

```php
<?php

/*

抽象工厂：提供一个创建一系列相关或相互依赖对象的接口。

注意：这里和工厂方法的区别是：一系列，而工厂方法则是一个。

那么，我们是否就可以想到在接口create里再增加创建“一系列”对象的方法呢？

*/

interface people {

	function marry();

}

class Oman implements people{

	function marry() {

		echo '美女，和我结婚吧！<br>';

	}

}

class Iman implements people{

	function marry() {

		echo '美女，你好漂亮啊！<br>';

	}

}

class Owomen implements people {

	function marry() {

		echo '我要戴钻戒、穿婚纱！<br>';

	}

}

class Iwomen implements people {

	function marry() {

		echo '我好害羞哦！！<br>';

	}

}

interface createMan { // 注意了，这里是本质区别所在，将对象的创建抽象成一个接口。

	function createOpen(); //外向

	function createIntro(); //内向

}

class FactoryMan implements createMan{

	function createOpen() {

		return new Oman;

	}

	function createIntro() {

		return new Iman;

	}

}

class FactoryWomen implements createMan {

	function createOpen() {

		return new Owomen;

	}

	function createIntro() {

		return new Iwomen;

	}

}

class Client {

// 简单工厂里的静态方法

	function test() {

		$Factory = new FactoryMan;

		man = Factory->createOpen();

		$man->marry();

    	$man = $Factory->createIntro();
    	$man->marry();
    
    	$Factory = new FactoryWomen;
    	$man = $Factory->createOpen();
    	$man->marry();
    	
    	$man = $Factory->createIntro();
    	$man->marry();
    }

}

$f = new Client;

$f->test();

运行结果

美女，和我结婚吧！

美女，你好漂亮啊！

我要戴钻戒、穿婚纱！

我好害羞哦！！

```

## 1.3 注册模式

> 注册模式，解决全局共享和交换对象。

> 已经创建好的对象，挂在到某个全局可以使用的数组上，在需要使用的时候，直接从该数组上获取即可。

> 将对象注册到全局的树上,任何地方直接去访问。

```php
<?php

class Register

{

    static protected $objects;//全局树  array

    //设置
    static function set($alias,$object)
    {
        self::$objects[$alias] = $object;
    }
    //获得
    static function get($alias)
    {
        return self::$objects[$alias];
    }
    //注销
    static function _unset($alias)
    {
        unset(self::$objects[$alias]);
    }

}

Register::set('conf',array('dbhost'=>'127.0.0.1'));

print_r(Register::get('conf'));

运行结果：

Array ( [dbhost] => 127.0.0.1 )

```

## 1.4 适配器模式

将各种截然不同的函数接口封装成统一的API
适配器模式核心思想：

把对某些相似的类的操作转化为一个统一的“接口”(这里是比喻的说话)--适配器，或者比喻为一个“界面”，统一或屏蔽了那些类的细节。

适配器模式还构造了一种“机制”，使“适配”的类可以很容易的增减，而不用修改与适配器交互的代码，符合“减少代码间耦合”的设计原则。

分类	优点	缺点
类适配器	代码简明，实现方便	有些冗杂
对象适配器	组合在耦合性上小于继承 ，对象适配器显得更加灵活	增加代码量

### 1.4.1 类适配器

类适配器中适配器继承原有的Adaptee类，自己实现原类没有的操作。

```php
<?php

interface ITarget  

{  

    function operation1();  

    function operation2();  

}  

interface IAdaptee  

{  

    function operation1();  

}  

class Adaptee implements IAdaptee  

{  

    public  function operation1()  

    {  

        echo "原子弹<br/>";  

    }  

}  

class Adapter extends Adaptee implements IAdaptee, ITarget  

{  

    public  function operation2()  

    {  

        echo "氢弹<br/>";  

    }  

}  

class Client  

{  

    public  function test()  

    {  

        $adapter = new Adapter();  

        $adapter->operation1();//原方法  

        $adapter->operation2();//适配方法  

    }  

}  

$test = new Client;

$test->test();

运行结果：

原子弹

氢弹

```



### 1.4.2 对象适配器

对象适配器使用的是组合模式，将adaptee作为adapter的一个引用

```php
interface ITarget  

{  

    function operation1();  

    function operation2();  

}  

interface IAdaptee  

{  

    function operation1();  

}  

class Adaptee implements IAdaptee  

{  

    public  function operation1()  

    {  

        echo "原子弹<br/>";  

    }  

}  

class Adapter implements  ITarget  

{  

    private $adaptee;  

    public function __construct($adaptee)  
    {  
        $this->adaptee = $adaptee;  
    }  
      
    public  function operation1()  
    {  
         return $this->adaptee->operation1();  
    }  
      
    public  function operation2()  
    {  
        echo "氢弹<br/>";  
    }  

}  

class Client  

{  

    public  function test()  

    {  

        $adapter = new Adapter(new Adaptee(null));  

        $adapter->operation1();//原方法  

        $adapter->operation2();//适配方法  

    }  

}  

$test = new Client;

$test->test();

运行结果：

原子弹

氢弹

```



## 1.5 策略模式

> 定义了算法族,分别封装起来，让它们之间可以互相替换，此模式让算法的变化独立于使用算法的客户。

原则：

 找出应用中可能需要变化之处，把它们独立出来，不要和那些不需要变化的代码混在一起;
 针对接口编程，不针对实现编程;
 多用组合，少用继承;

```php
<?php

interface marryBehavior{

    public function marry();

}

class marryWithMoney implements marryBehavior{

    public function marry(){

        echo "结婚要钱 <br/>";

    }

}

class marryWithNoMoney implements marryBehavior{

    public function marry(){

        echo "结婚不要钱 <br/>";

    }

}

class people{

    private $_marryBehavior;

    public function marryBehavior(){

        $this->_marryBehavior->marry();

    }

    public function setmarryBehavior(marryBehavior $behavior){
        $this->_marryBehavior = $behavior;
    }

}

class women extends people{

}

// Test Case

$dex = new women();

/*  结婚要钱 */

$dex->setmarryBehavior(new marryWithMoney());

$dex->marryBehavior();            

/*  结婚不要钱 */

$dex->setmarryBehavior(new marryWithNoMoney());

$dex->marryBehavior();

运行结果：

结婚要钱 

结婚不要钱 

```



## 1.6 观察者模式

观察者模式(Observer)，当一个对象状态发生变化时，依赖它的对象全部会收到通知，并自动更新。
应用场景	优点
一个事件发生后，要执行一连串更新操作。	观察者模式实现了低耦合，非侵入式的通知与更新机制

```php
<?php

class Paper{

    private $_observers = array();

    public function register($sub){ 
        /*  注册观察者 */
        $this->_observers[] = $sub;
    }

     

    public function trigger(){  
        /*  外部统一访问    */
        if(!empty($this->_observers)){
            foreach($this->_observers as $observer){
                $observer->update();
            }
        }
    }

}

/**
  * 观察者要实现的接口
  */
  interface Observerable{
  public function update();
  }

class Subscriber implements Observerable{

    public function update(){

        echo "用户1<br/>";

    }

}

class Subscriber1 implements Observerable{

    public function update(){

        echo "用户2<br/>";

    }

}

$paper = new Paper();

$paper->register(new Subscriber());

$paper->register(new Subscriber1());

$paper->trigger();

运行结果

用户1

用户2

```





## 1.7 原型模式

> 对象克隆以避免创建对象时的消耗



浅拷贝	

​	被拷贝对象的所有变量都含有与原对象相同的值，而且对其他对象的引用仍然是指向原来的对象，即浅拷贝只负责当前对象实例，对引用的对象不做拷贝。

深拷贝	

​	被拷贝对象的所有的变量都含有与原来对象相同的值，除了那些引用其他对象的变量，那些引用其他对象的变量将指向一个被拷贝的新对象，而不再是原来那些被引用的对象。即深拷贝把要拷贝的对象所引用的对象也拷贝了一次。而这种对被引用到的对象拷贝叫做间接拷贝。

序列化深拷贝

​	利用序列化来做深拷贝，把对象写到流里的过程是序列化的过程，这一过程称为“冷冻”或“腌咸菜”，反序列化对象的过程叫做“解冻”或“回鲜”

```php
<?php

interface Prototype

{

    public function shallowCopy();

    public function deepCopy();

}

class ConcretePrototype implements Prototype

{

    private $_name;

    public function __construct($name)

    {

        this->_name = name;

    }

    

    public function setName($name)
    {
        $this->_name = $name;
    }
    public function getName()
    {
        return $this->_name;
    }
    /**
     * 浅拷贝
     * */
    public function shallowCopy()
    {
        
        return clone $this;
        
    }
    /**
     * 深拷贝
     * */
    public function deepCopy()
    {
        $serialize_obj = serialize($this);
        $clone_obj = unserialize($serialize_obj);
        return $clone_obj;
    }

}

class Demo

{

    public $string;

}

class UsePrototype

{

    public function shallow()

    {

        $demo = new Demo();

        $demo->string = "浅拷贝";

        object_shallow_first = new ConcretePrototype(demo);

        object_shallow_second = object_shallow_first->shallowCopy();

        

        var_dump($object_shallow_first->getName());
        echo '<br/>';
        var_dump($object_shallow_second->getName());
        echo '<br/>';
         
        $demo->string = "浅浅拷贝";
        var_dump($object_shallow_first->getName());
        echo '<br/>';
        var_dump($object_shallow_second->getName());
        echo '<br/>';
    }
    
    public function deep()
    {
        $demo = new Demo();
        $demo->string = "深拷贝";
        $object_deep_first = new ConcretePrototype($demo);
        $object_deep_second = $object_deep_first->deepCopy();
    
        var_dump($object_deep_first->getName());
        echo '<br/>';
        var_dump($object_deep_second->getName());
        echo '<br/>';
    
        $demo->string = "深深拷贝";
        var_dump($object_deep_first->getName());
        echo '<br/>';
        var_dump($object_deep_second->getName());
        echo '<br/>';
    
    }

}

$up = new UsePrototype;

$up->shallow();

echo '<hr>';

$up->deep();

运行结果

object(Demo)#2 (1) { ["string"]=> string(9) "浅拷贝" } 

object(Demo)#2 (1) { ["string"]=> string(9) "浅拷贝" } 

object(Demo)#2 (1) { ["string"]=> string(12) "浅浅拷贝" } 

object(Demo)#2 (1) { ["string"]=> string(12) "浅浅拷贝" } 

object(Demo)#4 (1) { ["string"]=> string(9) "深拷贝" } 

object(Demo)#5 (1) { ["string"]=> string(9) "深拷贝" } 

object(Demo)#4 (1) { ["string"]=> string(12) "深深拷贝" } 

object(Demo)#5 (1) { ["string"]=> string(9) "深拷贝" } 

```



## 1.8 装饰器模式

> 装饰者模式动态地将责任附加到对象上。若要扩展功能，装饰者提供了比继承更有弹性的替代方案。

> 应用场景：一个类提供了一项功能，如果要在修改并添加额外的功能

 传统的编程模式，需要写一个子类继承它，并重写实现类的方法 ，
 使用装饰器模式，仅需要在运行时添加一个装饰器对象即可实现，可以实现最大额灵活性。

```sequence


    User ->> Waiter: Hello Waiter,give me a cup of coffee?

    Waiter-->>User: Do you add milk?

    Waiter--x User: price will add 0.2!

    User ->> Waiter:yes

    Waiter-->>User: Do you add sugar?

    Waiter--x User: price will add 0.2!

    User ->> Waiter:yes

    Note right of User: coffee:1.0 suger:0.2 milk:0.2 total:1.4;

```


​    

```php
<?php

/基类 被装饰者 饮料类/

abstract class Beverage{

    public $_name;

    abstract public function Cost();

}

/* 装饰者类 咖啡*/

class Coffee extends Beverage{

    public function __construct(){

        $this->_name = '咖啡';

    }   

    public function Cost(){

        return 1.00;

    }   

}

/* 以下三个类是装饰者相关类 */

/* 调味品装饰类 */

class CondimentDecorator extends Beverage{

    public function __construct(){

        $this->_name = '调味品';

    }   

    public function Cost(){

        return 0.1;

    }   

}

/牛奶/

class Milk extends CondimentDecorator{

    public $_beverage;

    public function __construct($beverage){

        $this->_name = '牛奶';

        if($beverage instanceof Beverage){

            this->_beverage = beverage;

        }else

            exit('Failure');

    }   

    public function Cost(){

        return $this->_beverage->Cost() + 0.2;

    }   

}

 /糖/

class Sugar extends CondimentDecorator{

    public $_beverage;

    public function __construct($beverage){

        $this->_name = '块糖';

        if($beverage instanceof Beverage){

            this->_beverage = beverage;

        }else{

            exit('Failure');

        }

    }

    public function Cost(){

        return $this->_beverage->Cost() + 0.2;

    }

}

//1.拿杯咖啡

$coffee = new Coffee();

//2.加点牛奶

coffee = new Milk(coffee);

//3.加点糖

coffee = new Sugar(coffee);

printf("Coffee Total:%0.2f元\n",$coffee->Cost());

```

