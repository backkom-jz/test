# 上传类

```php
<?php
namespace cokiy\framework;
class Upload
{
	public $uploadDir = './upload';
	public $isRandName = true;
	public $isdateDir = true;
	public $maxFileSize = 200 * 1024;
	public $path;
	public $uploadInfo;
	public $errorNo;
	public $allowSubfix = ['jpg','jpeg','pjpeg','wbmp','bmp','gif','png'];
	public $allowMime = ['image/png','image/wbmp','image/jpg','image/jpeg'];
	public $error = [
				-1 => '没有文件被上传.',
				-2 => '目录不存在且尝试创建时失败.',
				-3 => '修改目录权限失败.',
				-4 => '文件大小超出用户规定.',
				-5 => '文件后缀错误.',
				-6 => '文件MIME类型错误.',
				-7 => '不是上传文件.',
				-8 => '文件移动失败.',
				0 => '上传成功.',
				1 =>'上传的文件超过了php.ini中upload_max_filesize选项限制的值.',
				2=>'上传文件的大小超过了HTML表单中MAX_FILE_SIZE选项指定的值.',
				3=>'文件只有部分被上传.',
				4=>'没有文件被上传.',
				6=>'找不到临时文件夹.',
				7=>'文件写入失败.'
			];
	public function __construct(array $config = null)
	{
		if (!empty($config)) {
			foreach ($config as $key => $value) {
				if (property_exists(__CLASS__, $key)) {
					$this->$key = $value;
				}
			}
		}
		$this->uploadDir = $this->replaceSeperator($this->uploadDir);
	}
	/**
	 * [upload 文件上传函数，类外可直接调用]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-14
	 * @param   [type]     $file [form表单中input的名字]
	 * @return  [type]           [执行成功返回文件路径，否则返回false]
	 */
	public function upload($file)
	{
		// 1检查上传信息
		if (!$this->checkUploadInfo($file))
		{
			return false;
		}
		// 2检查上传目录
		if (!$this->checkDir($this->uploadDir)) {
			return false;
		}
		// 3检查标准上传错误（系统规定）
		if (!$this->checkSystemError()) {
			return false;
		}
		// 4检查自定义的错误（大小、后缀、MIME）
		if (!$this->checkCustomError()) {
			return false;
		}
		// 5判断是否是上传文件
		if (!$this->checkIsUploadFile()) {
			return false;
		}
		// 6移动文件到指定目录
		if (!$this->moveFile()) {
			return false;
		}
		return $this->path;
	}
	/**
	 * [getError 获得错误信息]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-14
	 * @return  [type]     [返回错误信息]
	 */
	public function getError()
	{
		return $this->error[$this->errorNo];
	}
	/**
	 * [moveFile 移动文件到指定文件夹]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-14
	 * @return  [type]     [移动成功返回路径，否则返回false]
	 */
	protected function moveFile()
	{
		// 1拼接目录
		$path = $this->uploadDir;
		// 是否启用日期文件夹
		if ($this->isdateDir) {
			$path .= date('Y/m/d');
			if (!is_dir($path)) {
				mkdir($path,0777,true);
			}
			$path .= '/';
		}
		// echo $this->uploadDir . '<br />';
		// echo $path . '<br />';
		// 2是否随机文件名
		if ($this->isRandName) {
			$path .= uniqid() . '.' . $this->getSubfix($this->uploadInfo['name']);
		} else {
			$path .= $this->uploadInfo['name'];
		}
		// echo $path . '<br />';
		// die;
		// 3移动文件
		if (!move_uploaded_file($this->uploadInfo['tmp_name'], $path)) {
			$this->errorNo = -8;
			return false;
		} 
		$this->path = $path;
		return true;
	}
	/**
	 * [checkIsUploadFile 检测是否是上传文件]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-14
	 * @return  [type]     [是上传文件返回true，否则返回false]
	 */
	protected function checkIsUploadFile()
	{
		if (!is_uploaded_file($this->uploadInfo['tmp_name'])) {
			$this->errorNo = -7;
			return false;
		}
		return true;
	}
	/**
	 * [checkCustomError 检测是否符合用户自定义要求]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-14
	 * @return  [type]     [符合返回true，否则返回false]
	 */
	protected function checkCustomError()
	{
		// 1检测文件大小是否超过规定
		if ($this->uploadInfo['size'] > $this->maxFileSize) {
			$this->errorNo = -4;
			return false;
		}
		// 2检测文件后缀是否在规定范围内
		if (!in_array($this->getSubfix($this->uploadInfo['name']), $this->allowSubfix)) {
			$this->errorNo = -5;
			return false;
		}
		// 3mime类型检测
		if (!in_array($this->uploadInfo['type'], $this->allowMime)) {
			$this->errorNo = -6;
			return false;
		}
		return true;
	}
	/**
	 * [getSubfix 得到文件后缀信息]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-14
	 * @param   [type]     $file [form表单中input的名字]
	 * @return  [type]           [返回文件后缀]
	 */
	protected function getSubfix($file)
	{
		return pathinfo($file)['extension'];
	}
	/**
	 * [checkSystemError 检测是否符合系统要求]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-14
	 * @return  [type]     [符合返回true，否则返回false]
	 */
	protected function checkSystemError()
	{
		$this->errorNo = $this->uploadInfo['error'];
		if (0 == $this->errorNo) {
			return true;
		} else {
			return false;
		}
	}
	/**
	 * [checkDir 检查目录是否存在以及是否可读可写]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-13
	 * @param   [type]     $dir [目录名]
	 * @return  [type]          [创建或修改成功返回true，否则返回false]
	 */
	protected function checkDir($dir)
	{
		if (!is_dir($dir)) {
			if (!mkdir($dir,0777)) {
				$this->errorNo = -2;
				return false;
			}
			return true;
		}
		if ((!is_readable($dir)) || (!is_writeable($dir))) {
			if (!chmod($dir,0777)) {
				$this->errorNo = -3;
				return false;
			}
		}
		return true;
	}
	/**
	 * [checkUploadInfo 检查上传信息]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-14
	 * @param   [type]     $file [form表单中input的名字]
	 * @return  [type]           [description]
	 */
	protected function checkUploadInfo($file)
	{
		// 1检测有没有上传信息
		if (empty($_FILES[$file])) {
			$this->errorNo = -1;
			return false;
		}
		// 2保存上传信息
		$this->uploadInfo = $_FILES[$file];
		return true;
	}
	/**
	 * [replaceSeperator 替换路径中的斜线，以适应linux环境]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-13
	 * @param   [type]     $dir [路径]
	 * @return  [type]          [返回替换后的路径]
	 */
	protected function replaceSeperator($dir)
	{
		$dir = str_replace('\\', '/', $dir);
		return $dir = rtrim($dir,'/') . '/';
	}
}
```

# 验证码类

```php
<?php
namespace cokiy\framework;
/**
 * 验证码,数字密码混合
 */
class VerifyCode
{
	protected $width = 80;
	protected $hight = 30;
	protected $length = 4;
	protected $imageType = 'png';
	protected $canvas;
	public $code;
	public function __construct($width = 80,$hight = 30,$length = 4,$imageType = 'png')
	{
		$this->width = ($width < 0) ? $this->width : $width;
		$this->hight = ($hight < 0) ? $this->width : $hight;
		$this->length = ($length < 3 || $length > 6) ? $this->length : $length;
		$this->imageType = $this->getImageType($imageType);
	}
	/**
	 * [getImageType 获得图片mime类型]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-12
	 * @param   [type]     $imageType [传入的类型]
	 * @return  [type]                [可识别的类型，否则退出]
	 */
	protected function getImageType($imageType)
	{
		$array = [
				'jpg' => 'jpeg',
				'pjpeg' => 'jpeg',
				'bmp' => 'wbmp',
				'png' => 'png'
		];
		if (array_key_exists($imageType,$array)) {
			return $imageType = $array[$imageType];
		} else {
			exit('图片格式不正确.');
		}
	}
	public function getCode()
	{
		return $this->code;
	}
	public function outputImage()
	{
		// 1创造画布
		$this->createImage();
		// 2生成验证码字符串
		$this->createCode();
		// 3画字符串
		$this->drawCode();
		// 4画干扰元素
		$this->drawInterferon();
		$this->drawLine();
		// 5输出到浏览器
		$this->sendImage();
		// 6销毁资源
		$this->destory();
	}
	protected function destory()
	{
		imagedestroy($this->canvas);
	}
	/**
	 * [drawInterferon 画干扰元素，点]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-12
	 * @return  [type]     [无]
	 */
	protected function drawInterferon()
	{
		for ($i=0; $i < 180; $i++) { 
			$x = rand(2,$this->width - 2);
			$y = rand(2,$this->hight - 2);
			imagesetpixel($this->canvas, $x, $y, $this->randColor(50,100));
		}
	}
	public function drawLine()
	{
		imageline($this->canvas, 0, 10, $this->width, $this->length, $this->randColor(50,90));
		imageline($this->canvas, ($this->width / 3), 0, ($this->width / 5 * 8), $this->width, $this->randColor(50,90));
	}
	/**
	 * [drawCode 画验证码字符串]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-12
	 * @return  [type]     [无]
	 */
	protected function drawCode()
	{
		for ($i=0; $i < $this->length; $i++) { 
			$x = 5 + $i * (($this->width - 5) / $this->length);
			$y = rand(2,($this->hight - 15));
			imagechar($this->canvas, 5, $x, $y, $this->code[$i], $this->randColor(1,80));
		}
	}
	/**
	 * [createCode 生成随机验证码，字母数字混合]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-12
	 * @return  [type]     [description]
	 */
	protected function createCode()
	{
		$this->code = substr(md5(rand() . ''),2,$this->length);
	}
	/**
	 * [sendImage 将图片输出到浏览器显示]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-12
	 * @return  [type]     [无]
	 */
	protected function sendImage()
	{
		header("content-type:image/" . $this->imageType);
		$funcName = 'image' . $this->imageType;
		if (function_exists($funcName)) {
			$funcName($this->canvas);
		} else {
			exit('不存在此类函数.');
		}
	}
	/**
	 * [createImage 创建画布]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-12
	 * @return  [type]     [无]
	 */
	protected function createImage()
	{
		$this->canvas = imagecreatetruecolor($this->width,$this->hight);
		$color = $this->randColor(127,200);
		imagefill($this->canvas, 0, 0, $color);
	}
	/**
	 * [randColor 产生随机颜色]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-12
	 * @return  [type]     [无]
	 */
	protected function randColor($low,$height)
	{
		return imagecolorallocate($this->canvas, rand($low,$height), rand($low,$height), rand($low,$height));
	}
	public function yzm($width=100,$height=30,$len=4,$imageType='png')
	{
		$obj = new self($width=100,$height=30,$len=4,$imageType='png');
		$obj->outputImage();
		return $obj->code;
	}
}
```

# Model类

```php
<?php
namespace cokiy\framework;
class Model
{
	protected $host;
	protected $user;
	protected $password;
	protected $dbname;
	protected $charset;
	protected $prefix;
	protected $link;
	public $sql;
	protected $cacheDir;
	public $cacheField;
	public $table = '';
	public $options = [
					'fields' => '*',
					'table' => '',
					'where' => '',
					'groupby' => '',
					'having' => '',
					'orderby' => '',
					'limit' => '',
					'values' => ''
				];
	public function __construct(array $config)
	{
		$this->host = $config['DB_HOST'];
		$this->user = $config['DB_USER'];
		$this->password = $config['DB_PASSWORD'];
		$this->dbname = $config['DB_NAME'];
		$this->charset = $config['DB_CHARSET'];
		$this->prefix = $config['DB_PREFIX'];
		$this->cacheDir = $config['DB_CACHE'];
		$this->table = $this->connect();
		$this->cacheDir = $config['DB_CACHE'];
		if (!$this->checkDir($this->cacheDir)) {
			exit('缓存目录不存在.');
		}
		$this->table = $this->prefix . $this->getTable();
		$this->cacheField = $this->getCacheFields();
		$this->options = $this->initOptions();
	}
	public function getTable()
	{
		$className= get_class($this);
		if (strpos($className,'\\') !== false) {
			$className = substr($className, strrpos($className,'\\')+1);
		} else {
			$className = substr($className, strrpos($className,'\\'));
		}
		$className = substr($className,0,-5);
		return lcfirst($className);
	}
	/**
	 * [getCacheFields 得到表里面所有字段名]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-14
	 * @return  [type]     [返回包含所有字段名的数组]
	 */
	public function getCacheFields()
	{
		$path = rtrim($this->cacheDir,'/') . '/' . $this->table . '.php';
		if (file_exists($path)) {
			return include $path;
		}
		$sql = 'desc ' . $this->table;
		$data = $this->query($sql,MYSQLI_BOTH);
		// var_dump($data);
		foreach ($data as $key => $value) {
			if ('PRI' == $value['Key']) {
				$fields['PRI'] = $value['Field'];
			}
			$fields[] = $value['Field'];
		}
		// var_dump($fields);
		$str = "<?php \n return " . var_export($fields,true) . ";?>";
		file_put_contents($path,$str);
		return include $path;
	}
	/**
	 * [initOptions 初始化sql语句所需内容，防止二次调用时冲突]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-14
	 * @return  [type]     [初始内容]
	 */
	protected function initOptions()
	{
		return [
					'fields' => '*',
					'table' => $this->table,
					'where' => '',
					'groupby' => '',
					'having' => '',
					'orderby' => '',
					'limit' => '',
					'values' => ''
				];
	}
	/**
	 * [checkDir 检查目录是否存在以及是否可读可写]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-13
	 * @param   [type]     $dir [目录名]
	 * @return  [type]          [创建或修改成功返回true，否则返回false]
	 */
	protected function checkDir($dir)
	{
		if (!is_dir($dir)) {
			if (!mkdir($dir,0777)) {
				return false;
			}
			return true;
		}
		if ((!is_readable($dir)) || (!is_writeable($dir))) {
			if (!chmod($dir,0777)) {
				return false;
			}
		}
		return true;
	}
	/**
	 * [connect 连接数据库]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-14
	 * @return  [type]     [无，直接给$this->link赋值]
	 */
	protected function connect()
	{
		$link = mysqli_connect($this->host,$this->user,$this->password);
		if (!$link) {
			exit('连接数据库失败');
		}
		$res = mysqli_select_db($link, $this->dbname);
		if (!$res) {
			exit('选择数据库失败');
		}
		$res = mysqli_set_charset($link, $this->charset);
		if (!$res) {
			exit('设置字符集失败');
		}
		$this->link = $link;
	}
	/**
	 * [where sql语句条件]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-14
	 * @param   [type]     $where [查询字段名，字符串或索引数组]
	 * @return  [type]            [返回$this,供连贯操作]
	 */
	public function where($where)
	{
		if (is_string($where)) {
			$this->options['where'] = ' where ' . $where;
		} elseif (is_array($where)) {
			$this->options['where'] = ' where ' . implode(',',$where);
		}
		return $this;
	}
	/**
	 * [groupby 结果集排序]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-14
	 * @param   [type]     $groupby [排序字段名，字符串或索引数组]
	 * @return  [type]              [返回$this,供连贯操作]
	 */
	public function groupby($groupby)
	{
		if (is_string($groupby)) {
			$this->options['groupby'] = ' group by ' . $groupby;
		} elseif (is_array($groupby)) {
			$this->options['groupby'] = ' group by ' . implode(',',$groupby);
		}
		return $this;
	}
	/**
	 * [having 结果集过滤]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-14
	 * @param   [type]     $having [过滤字段名，字符串或索引数组]
	 * @return  [type]             [返回$this,供连贯操作]
	 */
	public function having($having)
	{
		if (is_string($having)) {
			$this->options['having'] = ' having ' . $having;
		} elseif (is_array($having)) {
			$this->options['having'] = ' having ' . implode(',',$having);
		}
		return $this;
	}
	/**
	 * [orderby 结果集分组]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-14
	 * @param   [type]     $orderby [分组字段名，字符串或索引数组]
	 * @return  [type]              [返回$this,供连贯操作]
	 */
	public function orderby($orderby)
	{
		if (is_string($orderby)) {
			$this->options['orderby'] = ' order by ' . $orderby;
		} elseif (is_array($orderby)) {
			$this->options['orderby'] = ' order by ' . implode(',',$orderby);
		}
		return $this;
	}
	/**
	 * [limit 截取结果集]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-14
	 * @param   [type]     $limit [截取个数，字符串或索引数组]
	 * @return  [type]            [返回$this,供连贯操作]
	 */
	public function limit($limit)
	{
		if (is_string($limit)) {
			$this->options['limit'] = ' limit ' . $limit;
		} elseif (is_array($limit)) {
			$this->options['limit'] = ' limit ' . implode(',',$limit);
		}
		return $this;
	}
	/**
	 * [fields 获得要查找的字段]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-14
	 * @param   [type]     $fields [要查找的字段名，字符串类型]
	 * @return  [type]             [$this]
	 */
	public function fields($fields)
	{
		$this->options['fields'] = $fields;
		return $this;
	}
	/**
	 * [table 多表联查方法]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-20
	 * @param   string     $table [表名，可以是多个，字符串形式]
	 * @return  [type]            [返回$this,供连贯操作]
	 */
	public function table(string $table)
	{
		$tables = explode(',',$table);
		foreach ($tables as $key => $value) {
			$tbName = $this->prefix . ltrim($value,$this->prefix);
			$tables[$key] = $tbName;
		}
		$this->options['table'] = join(',',$tables);
		return $this;
	}
	/**
	 * [values 要更新的字段]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-14
	 * @param   [type]     $values [要更新的内容，关联数组]
	 * @return  [type]             [返回$this,供连贯操作]
	 */
	public function updatevalues(array $values)
	{
		$values = $this->addQuote($values);
		// var_dump($values);
		$data = $this->fieldsFilter($values);
		// var_dump($data);
		$str = '';
		foreach ($values as $key => $value) {
			if (array_key_exists($key, $data)) {
				$str .= $key . '=' . $value . ',';
			}
		}
		$str = rtrim($str,',');
		$this->options['values'] = $str;
		// var_dump($this->cacheField);
		// var_dump($data);
		// $this->options['values'] = $values;
		return $this;
	}
	/**
	 * [insertvalues 插入的字段]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-15
	 * @param   array      $values [插入的字段及内容，关联数组]
	 * @return  [type]             [返回$this,供连贯操作]
	 */
	public function insertvalues(array $values)
	{
		$values = $this->addQuote($values);
		$data = $this->fieldsFilter($values);
		$this->options['fields'] = implode(',',array_keys($data));
		$this->options['values'] = implode(',',array_values($data));
		return $this;
	}
	/**
	 * [fieldsFilter 过滤无效字段]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-15
	 * @param   [type]     $fields [字段及内容，关联数组]
	 * @return  [type]             [返回包含有效字段及值的数组]
	 */
	protected function fieldsFilter($fields)
	{
		$data = array_unique($this->cacheField);
		$data = array_flip($data);
		$data = array_intersect_key($fields,$data);
		return $data;
	}
	/**
	 * [select 查询]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-14
	 * @return  [type]     [返回结果集]
	 */
	public function select()
	{
		$sql = "select %fields% from %table% %where% %groupby% %having% %orderby% %limit%";
		$sql = str_replace(['%fields%',
							'%table%',
							'%where%',
							'%groupby%',
							'%having%',
							'%orderby%',
							'%limit%'],
						   ['fields' => $this->options['fields'],
							'table' => $this->options['table'],
							'where' => $this->options['where'],
							'groupby' => $this->options['groupby'],
							'having' => $this->options['having'],
							'orderby' => $this->options['orderby'],
							'limit' => $this->options['limit']],$sql);
		// var_dump($sql);
		return $this->query($sql,$resultType= MYSQLI_BOTH);
	}
	/**
	 * [query 查询操作时执行sql语句并返回结果集]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-14
	 * @param   [type]     $sql        [sql语句]
	 * @param   [type]     $resultType [返回结果集类型]
	 * @return  [type]                 [返回结果集或false]
	 */
	protected function query($sql,$resultType= MYSQLI_BOTH)
	{
		$this->sql = $sql;
		$this->options = $this->initOptions();
		$result = mysqli_query($this->link,$sql);
		if ($result && mysqli_affected_rows($this->link) > 0) {
			// return mysqli_fetch_all($result,$resultType);
			while ($record = mysqli_fetch_assoc($result)) {
				$data[] = $record;
			}
			return $data;
		}
		return false;
	}
	/**
	 * [insert 插入数据]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-14
	 * @return  [type]     [无，直接输出执行结果]
	 */
	public function insert()
	{
		$sql = "insert into %table%(%fields%) values(%values%)";
		// var_dump($fields);die;
		$sql = str_replace(['%fields%',
							'%table%',
							'%values%'],
						   ['fields' => $this->options['fields'],
							'table' => $this->options['table'],
							'values' => $this->options['values']],$sql);
		// echo $sql . '<br />';
		return $this->queryResult($sql);
	}
	/**
	 * [insert 删除数据]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-14
	 * @return  [type]     [无，直接输出执行结果]
	 */
	public function delete($isforce = false)
	{
		$sql = "delete from %table% %where%";
		$sql = str_replace(['%table%',
							'%where%'],
						   ['table' => $this->options['table'],
							'where' => $this->options['where']],$sql);
		// echo $sql . '<br />';
		if (empty($this->options['where']) && !$isforce) {
			exit('检测到没有删除条件将删除所有，如果确定执行，请在delete方法中添加参数true并重新执行。');
		}
		return $this->queryResult($sql);
	}
	public function update($isforce = false)
	{
		$sql = "update %table% set %values% %where% %groupby% %limit%";
		$sql = str_replace(['%table%',
							'%values%',
							'%where%',
							'%groupby%',
							'%limit%'],
						   ['table' => $this->options['table'],
						    'values' => $this->options['values'],
							'where' => $this->options['where'],
							'groupby' => $this->options['groupby'],
							'limit' => $this->options['limit']],$sql);
		// var_dump($sql);die;
		if (empty($this->options['where']) && !$isforce) {
			exit('检测到没有更新条件将更新所有，如果确定执行，请在update方法中添加参数true并重新执行。');
		}
		return $this->queryResult($sql);
		
	}
	/**
	 * [queryResult 除查询外的sql语句执行函数]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-14
	 * @param   [type]     $sql        [sql语句]
	 * @return  [type]                 [执行sql语句成功返回true，否则返回false]
	 */
	protected function queryResult($sql)
	{
		$this->sql = $sql;
		$this->options = $this->initOptions();
		$result = mysqli_query($this->link,$sql);
		if ($result) {
			return true;
		}
		return false;
	}
	protected function addQuote($values)
	{
		if (is_array($values)) {
			foreach ($values as $key => $value) {
				if (is_string($value)) {
					$values[$key] = "'$value'";
				}
			}
		}
		return $values;
	}
}
```

# Template类

```php
<?php
namespace cokiy\framework;
class Template
{
	protected $tplDir = './view';
	protected $cacheDir = './cache/app';
	protected $vars;
	protected $expireTime = 3600 * 24;
	public function __construct($tplDir = './view',$cacheDir = './cache/app',$expireTime = 3600)
	{
		$this->tplDir = $this->checkDir($tplDir);
		$this->cacheDir = $this->checkDir($cacheDir);
		$this->expireTime = $expireTime;
	}
	/**
	 * [assign 分配变量]
	 * @param  [type] $name  [变量名]
	 * @param  [type] $value [变量值]
	 * @return [type]        [没有]
	 */
	public function assign($name,$value)
	{
		$this->vars[$name] = $value;
	}
	/**
	 * [display 编译模板文件，加载缓存文件，显示]
	 * @param  [type] $viewFile  [模板文件名]
	 * @param  [type] $isExtract [是否还原变量]
	 * @return [type]            [无]
	 */
	public function display($viewFile,$isExtract = true)
	{
		//1 拼接模板文件和缓存文件的路径
		$tplFile = $this->tplDir . $viewFile;
		$cacheFile = $this->cacheDir . $this->replaceFileName($viewFile);
		//2 检测模板文件是否存在
		// var_dump($tplFile);
		if (!file_exists($tplFile)) {
			exit('模板文件不存在.');
		}
		//3 编译模板文件
		//3.1模板文件不存在或者模板文件修改时间晚于缓存文件创建时间
		if(!file_exists($cacheFile) || (filectime($cacheFile) < filemtime($tplFile)) || (filectime($cacheFile) + $this->expireTime < time() ) )
		{
			$this->checkDir(dirname($cacheFile));
			$content = file_get_contents($tplFile);
			$content = $this->compile($content);
			file_put_contents($cacheFile, $content);
		} else {
			$this->updateInclude($tplFile);
		}
	
		if ($isExtract) {
			extract($this->vars);
			include $cacheFile;
		}
	}
	protected function updateInclude($tplFile)
	{
		//读取模板文件内容
		$content = file_get_contents($tplFile);
		$pattern = '/\{include (.+)\}/';
		preg_match_all($pattern, $content, $matches);
		foreach ($matches[1] as $key => $value) {
			$value = trim($value ,'\'"');
			$this->display($value,false);
		}
	}
	protected function compile($content)
	{
		$rules = [
					'{$%%}' 			=>  '<?=$\1;?>',
					'{if %%}' 			=>  '<?php if(\1):?>',
					'{/if}'				=>  '<?php endif;?>',
					'{else}'			=>  '<?php else: ?>',
					'{elseif %%}'   	=>  '<?php elseif(\1):?>',
					'{else if %%}'  	=>  '<?php elseif(\1):?>',
					'{foreach %%}'		=>  '<?php foreach(\1):?>',
					'{/foreach}'		=>  '<?php endforeach;?>',
					'{while %%}'		=>  '<?php while(\1):?>',
					'{/while}'			=>  '<?php endwhile;?>',
					'{for %%}'			=>  '<?php for(\1):?>',
					'{/for}'			=>  '<?php endfor;?>',
					'{continue}'		=>  '<?php continue;?>',
					'{break}'			=>  '<?php break;?>',
					'{$%%++}'			=>  '<?php $\1++;?>',
					'{$%%--}'			=>  '<?php $\1--;?>',
					'{/*}'				=>  '<?php /*',
					'{*/}'				=>  '*/?>',
					'{section}'			=>  '<?php ',
					'{/section}'		=>  '?>',
					'{$%% = $%%}'		=>  '<?php $\1 = $\2;?>',
					'{default}'			=>  '<?php default:?>',
					'{include %%}'		=>  '<?php include "\1";?>'
					];
		foreach ($rules as $key => $value) {
			$key = preg_quote($key,'/');
			$pattern = '/' . str_replace('%%', '(.+)', $key) . '/ismU';
			if (stripos($value, 'include')) {
				$content = preg_replace_callback($pattern,[$this,'parseInclude'],$content);
			} else {
				$content = preg_replace($pattern, $value,$content);
			}
		}
		return $content;
	}
	public function parseInclude($data)
	{
		$file = trim($data[1],'\'"');
		$this->display($file,false);
		$cacheFile = $this->cacheDir . $this->replaceFileName($file);
		return "<?php include '$cacheFile';?>";
	}
	protected function replaceFileName($fileName)
	{
		return str_replace('.', '_', $fileName) . '.php';
	}
	/**
	 * [checkDir 检查目录是否存在以及是否可读可写]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-13
	 * @param   [type]     $dir [目录名]
	 * @return  [type]          [创建或修改成功返回true，否则返回false]
	 */
	protected function checkDir($dir)
	{
		$dir = $this->replaceSeperator($dir);
		$flag = true;
		if (!is_dir($dir)) {
			$flag = mkdir($dir,0777,true);
		} elseif ((!is_readable($dir)) || (!is_writeable($dir))) {
			$flag = chmod($dir,0777,true);
		}
		if (!$flag) {
			exit('目录不存在或不可读写.');
		}
		return $dir;
	}
	/**
	 * [replaceSeperator 替换路径中的斜线，以适应linux环境]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-13
	 * @param   [type]     $dir [路径]
	 * @return  [type]          [返回替换后的路径]
	 */
	protected function replaceSeperator($dir)
	{
		$dir = str_replace('\\', '/', $dir);
		return $dir = rtrim($dir,'/') . '/';
	}
}

```

# 分页类

```php
<?php
namespace cokiy\framework;
class Page
{
	public $totalCount;
	public $totalPage;
	public $countOfPage = 10;
	public $page;
	public $url;
	public $limit = 5;
	public $offset;
	public $max;
	public function __construct($totalCount,$countOfPage)
	{
		$this->totalCount = $totalCount;
		$this->limit = ($totalCount > $this->limit) ? $this->limit : $totalCount;
		$this->countOfPage = ($countOfPage > 0) ? $countOfPage : $this->countOfPage;
		$this->totalPage = ceil($totalCount / $countOfPage);
		$this->getPage();
		$this->getUrl();
		$this->getOffset();
	}
	public function outPager()
	{
		echo "<div class='yeshutiao'>";
			echo "<b><a href='" . $this->pre() . "'" . '>上一页</a></b>';
			for ($i = $this->offset; $i <= $this->max; $i++) { 
				echo "<b><a href='" . $this->setUrl($i) . "'" . '>' . $i . '</a></b> ';
			}
			echo "<b><a href='" . $this->next() . "'" . '>下一页</a></b>';
		echo "</div>";
	}
	public function getOffset()
	{
		$this->offset = $this->page - floor($this->limit / 2);
		if ($this->offset <= 1) {
			$this->offset = 1;
		}
		$this->max = $this->limit + $this->offset;
		if ($this->max > $this->totalPage) {
			$this->max = $this->totalPage;
			$tmp = $this->max - $this->limit;
			$this->offset = ($tmp <=1) ? 1 : $tmp;
		}
	}
	/**
	 * [first 返回第一页的url]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-14
	 * @return  [type]     [返回第一页的url]
	 */
	public function first()
	{
		return $this->setUrl(1);
	}
	/**
	 * [last 返回最后一页的url]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-14
	 * @return  [type]     [返回最后一页的url]
	 */
	public function last()
	{
		return $this->setUrl($this->totalPage);
	}
	/**
	 * [pre 返回上一页的url]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-14
	 * @return  [type]     [返回上一页的url]
	 */
	public function pre()
	{
		if ($this->page <= 1) {
			return $this->first();
		}
		return $this->setUrl($this->page - 1);
	}
	/**
	 * [next 返回下一页的url]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-14
	 * @return  function   [返回下一页的url]
	 */
	public function next()
	{
		if ($this->page >= $this->totalPage) {
			return $this->last();
		}
		return $this->setUrl($this->page + 1);
	}
	/**
	 * [setUrl 获得每一页的url]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-14
	 * @param   [type]     $page [返回每一页的url]
	 */
	public function setUrl($page)
	{
		if (stripos($this->url,'?')) {
			return  $this->url . '&page=' . $page;
		} else {
			return  $this->url . '?page=' . $page;
		}
	}
	/**
	 * [getUrl 取得当前页面的url]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-14
	 * @return  [type]     [无，直接将$this->url设置为当前页面的url]
	 */
	public function getUrl()
	{
		$url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . ':' .  $_SERVER['SERVER_PORT']; // $_SERVER['REQUEST_URI']
		$data = parse_url($_SERVER['REQUEST_URI']);
		
		if (isset($data['query'])) {
			parse_str($data['query'],$res);
			if (array_key_exists('page',$res)) {
				unset($res['page']);
			}
			$url .= $data['path'] . '?' . http_build_query($res);
		} else {
			$url .= $_SERVER['REQUEST_URI'];
		}
		$url = rtrim($url,'?'); 
		$this->url = $url;
		// echo $url . '<br />';
	}
	/**
	 * [getPage 获得当前页页码]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-14
	 * @return  [type]     [无，直接修改$this->page为当前页页码]
	 */
	public function getPage()
	{
		if (empty($_GET['page'])) {
			$this->page = 1;
		} else {
			$page = $_GET['page'];
			if ($page <= 1) {
				$this->page = 1;
			} elseif ($page >= $this->totalPage) {
				$this->page = $this->totalPage;
			} else {
				$this->page = $page;
			}
		}
	}
}
```

# 水印类

```php
<?php
namespace cokiy\framework;
class WaterMark
{
	public $saveDir = './image';
	public $imageType = 'png';
	public $isRandFileName = true;
	public $path;
	/**
	 * [__construct 构造函数，给成员属性赋值]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-13
	 * @param   [type]     $saveDir        [要保存的路径]
	 * @param   [type]     $imageType      [要保存的类型]
	 * @param   boolean    $isRandFileName [是否随机文件名]
	 */
	public function __construct($saveDir,$imageType,$isRandFileName = true)
	{
		// 替换目录中的斜线
		$this->saveDir = $this->replaceSeperator($saveDir);
		$this->saveDir = $saveDir;
		if (!$this->checkDir($saveDir)) {
			exit('目录不存在或不可读写.');
		}
		$this->isRandFileName = $isRandFileName;
	}
	/**
	 * [waterMark 给图片加水印]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-13
	 * @param   [type]     $dest   [目标图片]
	 * @param   [type]     $source [水印图片]
	 * @param   integer    $pos    [水印位置，九宫格或随机]
	 * @param   integer    $alpha  [水印透明度]
	 * @return  [type]             [无]
	 */
	public function waterMark($dest,$source,$pos = 5,$alpha = 100)
	{
		// 1路径检测
		if (!file_exists($dest) || !file_exists($source)) {
			exit('目标文件或水印文件不存在');
		}
		// 2计算图片尺寸
		list($destWidth,$destHeight) = getimagesize($dest);
		list($sourceWidth,$sourceHeight) = getimagesize($source);
		// var_dump($destWidth,$destHeight);
		// var_dump($sourceWidth,$sourceHeight);
		// die;
		if (($destWidth < $sourceWidth) || ($destHeight < $sourceHeight)) {
			exit('水印图片比目标图片大.');
		}
		// 3计算水印位置
		$postion = $this->getPosition($destWidth,$destHeight,$sourceWidth,$sourceHeight,$pos);
		// 4合并图片
		$destImage = $this->openImage($dest);
		$sourceImage = $this->openImage($source);
		if (!$destImage || !$sourceImage) {
			exit('无法打开图片文件.');
		}
		imagecopymerge($destImage, $sourceImage, $postion['x'], $postion['y'], 0, 0, $sourceWidth, $sourceHeight, $alpha);
		// 5保存图片
		$this->saveImage($destImage,$dest);
		// 6销毁资源
		imagedestroy($destImage);
		imagedestroy($sourceImage);
	}
	/**
	 * [zoom 图片缩放]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-13
	 * @param   [type]     $imageFile [要缩放的文件名]
	 * @param   [type]     $width     [预期宽度]
	 * @param   [type]     $height    [预期高度]
	 * @param   boolean    $isequal   [是否等比例缩放，默认true，即等比缩放]
	 * @return  [type]                [无]
	 */
	public function zoom($imageFile,$width,$height,$isequal = true)
	{
		// 1路径检测
		if (!file_exists($imageFile)) {
			exit('图片不存在.');
		}
		// 2计算缩放尺寸
		list($oldWidth,$oldHeight) = getimagesize($imageFile);
		$size = $this->scaleCul($oldWidth,$oldHeight,$width,$height,$isequal);
		// 3合并图片
		$oldImage = $this->openImage($imageFile);
		if ($isequal) {
			$destImage = imagecreatetruecolor($width, $height);
			imagecopyresampled($destImage, $oldImage, $size['x'], $size['y'], 0, 0, $size['newWidth'], $size['newHeight'], $oldWidth, $oldHeight);
		} else {
			$destImage = imagecreatetruecolor($width, $height);
			imagecopyresampled($destImage, $oldImage, $size['x'], $size['y'], 0, 0, $size['newWidth'], $size['newHeight'], $oldWidth, $oldHeight);
		}
		
		
		// 4保存图片
		$this->saveImage($destImage, $imageFile);
		// 释放资源
		imagedestroy($destImage);
		imagedestroy($oldImage);
	}
	/**
	 * [scaleCul 计算缩放后的图片宽高以及显示位置]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-13
	 * @param   [type]     $oldWidth  [图片原始宽度]
	 * @param   [type]     $oldHeight [图片原始高度]
	 * @param   [type]     $width     [预期宽度]
	 * @param   [type]     $height    [预期高度]
	 * @param   [type]     $isequal   [是否等比例缩放，默认true，即等比缩放]
	 * @return  [type]                [返回新的图片宽高以及显示坐标]
	 */
	protected function scaleCul($oldWidth,$oldHeight,$width,$height,$isequal)
	{
		$widthScale = $width / $oldWidth;
		$heightScale = $height / $oldHeight;
		$scale = min($widthScale,$heightScale);
		if ($isequal) {
			$newWidth = $oldWidth * $scale;
			$newHeight = $oldHeight * $scale;
		} else {
			$newWidth = $oldWidth * $widthScale;
			$newHeight = $oldHeight * $heightScale;
		}
		if ($widthScale < $heightScale) {
			$y = ($height - $newHeight) / 2;;
			$x = 0;
		} else {
			$y = 0;
			$x = ($width - $newWidth) / 2;
		}
		return [
				'newWidth' => $newWidth,
				'newHeight' => $newHeight,
				'x' => $x,
				'y' => $y
			];
	}
	/**
	 * [saveImage 保存添加过水印的图片]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-13
	 * @param   [type]     $image      [图像资源]
	 * @param   [type]     $originFile [文件名]
	 * @return  [type]                 [无]
	 */
	protected function saveImage($image,$originFile)
	{
		if ($this->isRandFileName) {
			$path = $this->saveDir . '/' . uniqid() . '.' . $this->imageType;
		} else {
			$path = $this->saveDir . '/' . pathinfo($originFile)['filename'] . '.' . $this->imageType;
		}
		$this->path = $path;
		// var_dump($path);
		// die;
		$funcName = 'image' . $this->imageType;
		if (function_exists($funcName)) {
			$funcName($image,$path);
		} else {
			exit('图片无法保存.');
		}
	}
	/**
	 * [openImage 打开图片]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-13
	 * @param   [type]     $file [文件名]
	 * @return  [type]           [成功返回图像资源，失败返回false]
	 */
	protected function openImage($file)
	{
		$type = exif_imagetype($file);
		$types = [0,'gif','jpeg','png','swf','psd','wbmp'];
		$funcName = 'imagecreatefrom' . $types[$type];
		return $funcName($file);
	}
	/**
	 * [getPosition 计算水印位置]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-13
	 * @param   [type]     $destWidth    [目标文件宽度]
	 * @param   [type]     $destHeight   [目标文件高度]
	 * @param   [type]     $sourceWidth  [水印图片宽度]
	 * @param   [type]     $sourceHeight [水印图片高度]
	 * @param   [type]     $pos          [位置1~9，超出此范围则随机位置]
	 * @return  [type]                   [返回水印左上角坐标]
	 */
	protected function getPosition($destWidth,$destHeight,$sourceWidth,$sourceHeight,$pos)
	{
		if (($pos < 1) || ($pos > 9)) {
			$x = rand(0,$destWidth - $sourceWidth);
			$y = rand(0,$destHeight - $sourceHeight);
		} else {
			$x = ($pos - 1) % 3 *($destWidth - $sourceWidth) / 2;
			$y = (int)(($pos - 1) / 3) * ($destHeight - $sourceHeight) / 2;
		}
		return ['x' => $x, 'y' => $y];
	}
	/**
	 * [checkDir 检查目录是否存在以及是否可读可写]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-13
	 * @param   [type]     $dir [目录名]
	 * @return  [type]          [创建或修改成功返回true，否则返回false]
	 */
	protected function checkDir($dir)
	{
		if (!is_dir($dir)) {
			return mkdir($dir,0777);
		}
		if ((!is_readable($dir)) || (!is_writeable($dir))) {
			chmod($dir,0777);
		}
		return true;
	}
	/**
	 * [replaceSeperator 替换路径中的斜线，以适应linux环境]
	 * @author cokiy
	 * @version [1.0]
	 * @date    2017-06-13
	 * @param   [type]     $dir [路径]
	 * @return  [type]          [返回替换后的路径]
	 */
	protected function replaceSeperator($dir)
	{
		$dir = str_replace('\\', '/', $dir);
		return $dir = rtrim($dir,'/') . '/';
	}
}
```

