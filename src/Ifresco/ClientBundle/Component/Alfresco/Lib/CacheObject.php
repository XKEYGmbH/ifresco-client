<?php  
namespace Ifresco\ClientBundle\Component\Alfresco\Lib;

class CacheObject
{
   protected $key = null;
   protected $cache = null;

    public function __construct($cache = null){
        $this->cache = $cache;
    }

    public function call($callable, $arguments = array(), $key = null)
    {
      $this->key = $key;


      return $this->doCall($callable, $arguments);
    }

    public function computeCacheKey($callable, $arguments = array())
    {
       return (null !== $this->key) ? $this->key : $this->doComputeCacheKey($callable, $arguments);
    }

    public function remove($key)
    {
      return $this->cache->delete($key);
    }

    public function doCall($callable, $arguments = array())
    {
        // Generate a cache id
        $key = $this->computeCacheKey($callable, $arguments);

        $serialized = $this->cache->fetch($key);


        if ($serialized !== false)
        {
            //no unserialization is required
            $data = $serialized;
            //echo $key.'<br />';
        }
        else
        {

            if (!is_callable($callable))
            {
                throw new \Exception('The first argument to call() must be a valid callable.');
            }

            ob_start();
            ob_implicit_flush(false);

            try
            {
                $data = call_user_func_array($callable, $arguments);
            }
            catch (\Exception $e)
            {
                ob_end_clean();
                throw $e;
            }

            $data_output = ob_get_clean();

            $this->cache->save($key, $data);
        }

        //echo $data_output;

        return $data;
    }

    public function doComputeCacheKey($callable, $arguments = array())
    {
        return md5(serialize($callable).serialize($arguments));
    }
}
?>