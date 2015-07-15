<?php
/**
 * 自定义自动加载类: 非Composer提供的自定义加载类
 *
 * 使用示例：
 *
 *  程序根目录引入自动加载文件
 *  require_once(PROJECT_ROOT . 'Vendor/cbootstrap/Autoloader.php');
 *  初始化自动加载：spl_autoload_register
 *  \cbootstrap\Autoloader::instance()->init();
 *  使用时进行加载：命名空间由spl_autoload_register进行加载
 *  \MNLogger\TraceLogger::instance('trace')->HTTP_SR();
 */

namespace CBootstrap;

/**
 * 类库自动加载类
 */
class Autoloader{
    protected static $sysRoot = array();
    protected static $instance;
    protected $classPrefixes = array();
    protected function __construct()
    {
        static::$sysRoot = array(
            //默认的项目根目录
            __DIR__.'/../../',
            // Vendor目录
            __DIR__.'/../'
        );
    }

    /**
     * @return self
     */
    public static function instance()
    {
        if(!static::$instance)
        {
            static::$instance = new static;
        }
        return static::$instance;
    }

    /**
     * 添加根目录. 默将使用Autoloader目录所在的上级目录为根目录。
     *
     * @param string $path
     * @return self
     */
    public function addRoot($path)
    {
        static $called;
        if(!$called)
        {
            // 取消默认的项目根目录
            unset(static::$sysRoot[0]);
            $called = true;
        }
        static::$sysRoot[] = $path;
        return $this;
    }

    /**
     * 按命名空间自动加载相应的类.
     *
     * @param string $name 命名空间及类名
     * @return boolean
     */
    public function loadByNamespace($name)
    {
        $classPath = str_replace('\\', DIRECTORY_SEPARATOR ,$name);

        foreach(static::$sysRoot as $k => $root)
        {
            $classFile = $root.$classPath.'.php';
            if(is_file($classFile))
            {
                require_once($classFile);
                if(class_exists($name, false)) {
                    return true;
                }
            }
            else
            {// 对thrift provider文件的支持
                $interfaceStr = substr($name, strlen($name)-2);
                if(strpos($name, 'Provider\\') === 0 && $interfaceStr === 'If')
                {
                    $classFile = substr_replace($classFile, '', strlen($classFile)-6, 2);
                    if(is_file($classFile))
                    {
                        require_once($classFile);
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @return $this
     */
    public function init()
    {
        spl_autoload_register(array($this, 'loadByNamespace'));
        return $this;
    }
}
