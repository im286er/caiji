# caiji
一个使用PHP编写的采集类
#
快速开始

**引入文件**

```
require_once 'Caiji.php';
```

**初始化对象**

```
$caiji = new CaiJi($url);
```

**设置相关采集参数**

设置浏览器agent

```
$caiji->setAgent('Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)');
```

设置cookie

```
$caiji->setCookieFile('cookie.txt');
```

设置ip

```
$caiji->setIp('220.181.68.'.rand(100,200));
```

设置超时时间

```
$caiji->setTime($ruleConfig['timeOut']);
```

设置来源

```
$caiji->setRefer('http://www.baidu.com');
```

如果采集https网页内容，需要加上下面的代码

```
$caiji->setSsl(true);
```

获的采集数据

```
echo $caiji->getRes();
```
