# mysql 事务以及表锁

## 简单mysql事务

> MYSQL默认是自动提交的，也就是你提交一个QUERY，它就直接执行！
>
> 我们可以通过 set autocommit=0 禁止自动提交 set autocommit=1 开启自动提交 来实现事务的处理。
>
>  当你用 set autocommit=0 的时候，你以后所有的SQL都将做为事务处理，直到你用commit确认或rollback结束。 
>
> 注意当你结束这个事务的同时也开启了个新的事务

```php
<?php
$db = new mysqli("localhost","root","XXXXXX","laravel5"); //连接数据库

$db->autocommit(false); //设置为非自动提交——事务处理

$sql1  = "INSERT INTO `laravel5`.`users` (`name` )VALUES ('南慕容' )";

$result1 = $db->query($sql1);

$sql2  = "INSERT INTO `laravel5`.`users` (`name` )VALUES ('北乔峰')";

$result2 = $db->query($sql2);

if ($result1 && $result2) {

    $db->commit();  //全部成功，提交执行结果

    echo '提交';

} else {

    $db->rollback(); //有任何错误发生，回滚并取消执行结果

    echo '回滚';

}

$db->autocommit(true); //设置为自动提交——事务处理

$db->close();

?>
```

## 简单表锁

>  LOCK TABLES可以锁定用于当前线程的表。
>
> 如果表被其它线程锁定，则造成堵塞，直到可以获取所有锁定为止。 
>
> UNLOCK TABLES可以释放被当前线程保持的任何锁定。
>
> 当线程发布另一个LOCK TABLES时，或当与服务器的连接被关闭时，所有由当前线程锁定的表被隐含地解锁。

```php
mysql_query("LOCK TABLES `user` WRITE");//锁住`user`表
$sql = "INSERT INTO `user` (`id`, `username`, `sex`) VALUES (NULL, 'test1', '0')";
$res = mysql_query($sql);
if($res){
echo '提交成功。!';
}else{
echo '失败!';
}
mysql_query("UNLOCK TABLES");//解除锁定
```

