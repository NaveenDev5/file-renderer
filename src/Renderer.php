<?php
namespace Rotexsoft\FileRenderer;

/**
 * 
 * Class for rendering the contents of a php file.
 *
 * @link      https://github.com/rotexsoft/file-renderer
 * @copyright Copyright (c) 2015-2016
 * @license   BSD-2-Clause https://github.com/rotexsoft/file-renderer/blob/master/LICENSE
 * 
 * @author Rotimi Adegbamigbe
 * 
 */
class Renderer
{
    /**
     * 
     * Path(s) to directorie(s) containing (*.php) files to be rendered via this 
     * class. 
     * 
     * These paths will be searched when rendering a file with an instance of this class.
     * The order of the search is from the first to the last element of this array.
     *
     * @var array
     *  
     */
    protected $file_paths;

    /**
     *
     * Name of php a file to be rendered. 
     * 
     * If path is not prepended to the name of the file, the file will be 
     * searched for in the list of paths registered in $this->file_paths.
     * 
     * It could be left blank (which means that a file name must supplied when
     * calling any of the render* methods).
     * 
     * @var string 
     * 
     */
    protected $file_name;
    
    /**
     * 
     * An array of data to be extracted into variables for use in the php file 
     * to be rendered via an instance of this class.
     *
     * @var array  
     *             
     */
    protected $data;
    
    /**
     * 
     * An array of keys in $this->data whose values (only strings) will be individually escaped using 
     * Zend\Escaper\Escaper::escapeHtml($string).
     * 
     * Set this for keys in $this->data with values (only strings) like html tags and the likes (anything
     * you would normally escape via htmlspecialchars).
     * 
     * @var array
     * 
     */
    protected $data_vars_2_html_escape = array();
    
    /**
     * 
     * An array of keys in $this->data whose values (only strings) will be individually escaped using 
     * Zend\Escaper\Escaper::escapeHtmlAttr($string).
     * 
     * Set this for keys in $this->data with values (only strings) that will be rendered as attributes
     * within html elements.
     * 
     * @var array
     * 
     */
    protected $data_vars_2_html_attr_escape = array();
    
    /**
     * 
     * An array of keys in $this->data whose values (only strings) will be individually escaped using 
     * Zend\Escaper\Escaper::escapeCss($string).
     * 
     * Set this for keys in $this->data with values (only strings) that will be rendered inside css style 
     * tags or inside the style attribute of any html element. 
     * 
     * CSS escaping via Zend\Escaper\Escaper::escapeCss($string) excludes only basic 
     * alphanumeric characters and escapes all other characters into valid CSS 
     * hexadecimal escapes.
     * 
     * @var array
     * 
     */
    protected $data_vars_2_css_escape = array();
    
    /**
     * 
     * An array of keys in $this->data whose values (only strings) will be individually escaped using 
     * Zend\Escaper\Escaper::escapeJs($string).
     * 
     * Set this for keys in $this->data with values (only strings) that will be rendered as Javascript 
     * data values (eg. Javascript string literals).
     * 
     * Javascript escaping via Zend\Escaper\Escaper::escapeJs($string) applies to all 
     * literal strings & digits. It is not possible to safely escape other Javascript 
     * markup.
     * 
     * @var array
     * 
     */
    protected $data_vars_2_js_escape = array();
    
    /**
     * 
     * Encoding to be used for escaping data values in $this->data. 
     * It is used in conjunction with $this->data_vars_2_html_escape, 
     * $this->data_vars_2_html_attr_escape, $this->data_vars_2_css_escape 
     * and $this->data_vars_2_js_escape. 
     * 
     * The default value is 'utf-8'.
     *      
     * Below is a list of supported encodings:
     *      
     *      'iso-8859-1',
     *      'iso8859-1',
     *      'iso-8859-5',
     *      'iso8859-5',
     *      'iso-8859-15',
     *      'iso8859-15',
     *      'utf-8',
     *      'cp866',
     *      'ibm866',
     *      '866',
     *      'cp1251',
     *      'windows-1251',
     *      'win-1251',
     *      '1251',
     *      'cp1252',
     *      'windows-1252',
     *      '1252',
     *      'koi8-r',
     *      'koi8-ru',
     *      'koi8r',
     *      'big5',
     *      '950',
     *      'gb2312',
     *      '936',
     *      'big5-hkscs',
     *      'shift_jis',
     *      'sjis',
     *      'sjis-win',
     *      'cp932',
     *      '932',
     *      'euc-jp',
     *      'eucjp',
     *      'eucjp-win',
     *      'macroman'
     * 
     * @var string
     * 
     */
    protected $escape_encoding = 'utf-8';
    
    /**
     * 
     * This array keeps track of the hashes of the data arrays that have been escaped via 
     * $this->escapeData(). THIS WILL ALLOW $this->escapeData() TO ONLY ESCAPE EACH UNIQUE
     * DATA ARRAY ONLY ONCE, SUBSEQUENT CALLS TO $this->escapeData() FOR THE SAME DATA 
     * ARRAY WITH THE SAME ESCAPE PARAMETER ARRAYS SHOULD DO NOTHING TO THE DATA ARRAY.
     * We only need to escape desired values in an array only once.
     * 
     * @var array
     * 
     */
    public $multi_escape_prevention_guard = array();

    /**
     * 
     * @param string $file_name name of a php file to be rendered. If path is not 
     *                          prepended to the name of the file, the file will 
     *                          be searched for in the list of paths registered 
     *                          in $this->file_paths. It can be left blank.
     * 
     * @param array $data An array of data to be extracted into variables for 
     *                    use in the php file to be rendered via an instance of 
     *                    this class.
     * 
     * @param array $file_paths An array of path(s) to directorie(s) containing 
     *                          (*.php) files to be rendered via this class.
     * 
     * @param string $escape_encoding Encoding to be used for escaping data values in $this->data.
     *                                See documentation for $this->escape_encoding for more info.
     *                                  
     * @param array $data_vars_2_html_escape An array of keys in $this->data whose values (only strings) 
     *                                       will be individually escaped using Zend\Escaper\Escaper::escapeHtml($string). 
     *                                       Set this for keys in $this->data with values (only strings) like html tags and 
     *                                       the likes (anything you would normally escape via htmlspecialchars).
     * 
     * @param array $data_vars_2_html_attr_escape An array of keys in $this->data whose values (only strings) 
     *                                            will be individually escaped using Zend\Escaper\Escaper::escapeHtmlAttr($string). 
     *                                            Set this for keys in $this->data with values (only strings) that will be rendered  
     *                                            as attributes within html tags.
     * 
     * @param array $data_vars_2_css_escape An array of keys in $this->data whose values (only strings) will be individually 
     *                                      escaped using Zend\Escaper\Escaper::escapeCss($string). Set this for keys in 
     *                                      $this->data with values (only strings) that will be rendered inside css style 
     *                                      tags or inside the style attribute of any html element. CSS escaping via 
     *                                      Zend\Escaper\Escaper::escapeCss($string) excludes only basic alphanumeric 
     *                                      characters and escapes all other characters into valid CSS hexadecimal escapes.
     * 
     * @param array $data_vars_2_js_escape An array of keys in $this->data whose values (only strings) will be individually 
     *                                     escaped using Zend\Escaper\Escaper::escapeJs($string). Set this for keys in 
     *                                     $this->data with values (only strings) that will be rendered as Javascript 
     *                                     data values (eg. Javascript string literals). Javascript escaping via 
     *                                     Zend\Escaper\Escaper::escapeJs($string) applies to all literal strings 
     *                                     and digits. It is not possible to safely escape other Javascript markup.
     * 
     * @throws \InvalidArgumentException
     */
    public function __construct(
            $file_name='', 
            array $data = array(), 
            array $file_paths = array(), 
            $escape_encoding = 'utf-8',
            array $data_vars_2_html_escape = array(),
            array $data_vars_2_html_attr_escape = array(),
            array $data_vars_2_css_escape = array(),
            array $data_vars_2_js_escape = array()
    ) {
        if( !is_string($file_name) ) {
            
            $msg = "ERROR: ". get_class($this) ."::__construct(...) expects first parameter (the name of the php file to be rendered) to be a `string`." 
                   . PHP_EOL .'`'. $this->getVarType($file_name, true).'` was supplied with the value below:'
                   . PHP_EOL . var_export($file_name, true). PHP_EOL ;
            
            throw new \InvalidArgumentException($msg);
        }
        
        $this->data = $data;
        $this->file_name = $file_name;
        $this->file_paths = $file_paths;
        $this->escape_encoding = $escape_encoding;
        $this->data_vars_2_js_escape = $data_vars_2_js_escape;
        $this->data_vars_2_css_escape = $data_vars_2_css_escape;
        $this->data_vars_2_html_escape = $data_vars_2_html_escape;
        $this->data_vars_2_html_attr_escape = $data_vars_2_html_attr_escape;
    }
    
    /**
     * 
     * Adds a new item to the $this->data array.
     * 
     * @param string $key key for a data value to be added to the $this->data array.
     * @param  mixed $value a data value to be added to the $this->data array.
     * 
     * @return void
     */
    public function __set($key, $value) {
        
        $this->data[$key] = $value;
    }

    /**
     * 
     * Retreives a data value associated with the given key in the $this->data array.
     * 
     * @param string $key key for a data value to be retreived from the $this->data array.
     * 
     * @return mixed a data value associated with the given key in the $this->data array.
     * 
     * @throws \Exception if item with specified key is not in the $this->data array.
     */
    public function __get($key) {
        
        if ( isset($this->data[$key]) ) {

            return $this->data[$key];
            
        } else {

            $msg = "ERROR: Item with key '$key' does not exist in " 
                   . get_class($this) .'.'. PHP_EOL . var_export($this, true);
            
            throw new \Exception($msg);
        }
    }
    
    /**
     * 
     * Checks if the given key is set in the $this->data array.
     * 
     * @param string $key potential key for an item in the $this->data array.
     * 
     * @return bool true if item with given key is set in $this->data, else false.
     */
    public function __isset($key) {
        
        return isset($this->data[$key]);
    }
    
    /**
     * 
     * Unset the item with the given key in the $this->data array.
     * 
     * @param string $key potential key for an item in the $this->data array.
     * 
     * @return void
     */
    public function __unset($key) {
        
        if( isset($this->data[$key]) ) {
            
            unset($this->data[$key]);
        }
    }
    
    /**
     * 
     * @see documentation for $this->__set($key, $value).
     */
    public function setVar($key, $value) {
        
        $this->__set($key, $value);
    }
    
    /**
     * 
     * @see documentation for $this->__get($key).
     */
    public function getVar($key) {
        
        return $this->__get($key);
    }

    /**
     * 
     * Returns the value of the `file_paths` property of an instance of this class.
     * 
     * @return array the array of path(s) to directorie(s) containing (*.php) 
     *               files to be rendered via this class.
     * 
     */
    public function getFilePaths() {
        
        return $this->file_paths;
    }

    /**
     * 
     * Returns the value of the `data` property of an instance of this class.
     * 
     * @return array An array of data to be extracted into variables for use in the php file
     *               to be rendered via an instance of this class.
     * 
     */
    public function getData() {
        
        return $this->data;
    }
    
    /**
     * 
     * Add a path to the end of the array of path(s) to directorie(s) containing 
     * (*.php) files to be rendered via this class.
     * 
     * @param string $path a path to the end of the $this->file_paths array.
     * 
     * @return void
     */
    public function appendPath( $path ) {
        
        $this->file_paths[] = $path;
    }

    /**
     * 
     * Add a path to the beginning of the array of path(s) to directorie(s) 
     * containing (*.php) files to be rendered via this class.
     * 
     * @param string $path a path to the beginning of the $this->file_paths array.
     * 
     * @return void
     */
    public function prependPath( $path ) {
        
        array_unshift($this->file_paths, $path);
    }
    
    /**
     * 
     * Removes the first `n` elements in the array of path(s) to directorie(s) 
     * containing (*.php) files to be rendered via this class.
     * 
     * @param int $number_of_paths_2_remove number of elements to remove from the front of $this->file_paths.
     * 
     * @return array an array of the removed elements.
     */
    public function removeFirstNPaths($number_of_paths_2_remove) {
        
        $removed_paths = array();
        
        if( is_numeric($number_of_paths_2_remove) ) {
            
            $number_of_paths_2_remove = (int) $number_of_paths_2_remove;
            
            while ( 
                $number_of_paths_2_remove > 0  
                && count($this->file_paths) > 0 
            ) {
                $removed_path = array_shift($this->file_paths);
                
                if( !is_null($removed_path) ) {
                    
                    $removed_paths[] = $removed_path;
                }
                
                $number_of_paths_2_remove--;
            }
        }
        
        return $removed_paths;
    }
    
    /**
     * 
     * Removes the last `n` elements in the array of path(s) to directorie(s) 
     * containing (*.php) files to be rendered via this class.
     * 
     * @param string $number_of_paths_2_remove number of elements to remove from the back of $this->file_paths.
     * 
     * @return array an array of the removed elements.
     */
    public function removeLastNPaths($number_of_paths_2_remove) {
        
        $removed_paths = array();
        
        if( is_numeric($number_of_paths_2_remove) ) {
            
             $number_of_paths_2_remove = (int) $number_of_paths_2_remove;
            
            while ( 
                $number_of_paths_2_remove > 0  
                && count($this->file_paths) > 0 
            ) {
                $removed_path = array_pop($this->file_paths);
                
                if( !is_null($removed_path) ) {
                    
                    $removed_paths[] = $removed_path;
                }
                
                $number_of_paths_2_remove--;
            }
        }
        
        return array_reverse($removed_paths);
    }
    
    /**
     * 
	 * Captures and returns the output that is generated when a php file is included. 
     * 
     * @param string $file_name Name of php file to be included (with/without
     *                          the directory path). If the directory path is 
     *                          included & the file exists, it is included and
     *                          the output is returned. If the directory path is 
     *                          not included, the file will be searched for from 
     *                          the list of directories registered in 
     *                          $this->file_paths.
     *                          
     *                          If $file_name still can't be found or is an empty 
     *                          string or is not supplied, this method tries to 
     *                          locate $this->file_name if possible and renders 
     *                          it instead.
     *                          
     * @param array $data Array of data to be extracted to make local variables.
     *                    It is combined together with $this->data. It will
     *                    overwrite item(s) with the same key in $this->data.
     * 
     * @param string $escape_encoding Encoding to be used for escaping data values in $data and $this->data.
     *                                Below is a list of supported encodings:  
     *                                  'iso-8859-1',
     *                                  'iso8859-1',
     *                                  'iso-8859-5',
     *                                  'iso8859-5',
     *                                  'iso-8859-15',
     *                                  'iso8859-15',
     *                                  'utf-8',
     *                                  'cp866',
     *                                  'ibm866',
     *                                  '866',
     *                                  'cp1251',
     *                                  'windows-1251',
     *                                  'win-1251',
     *                                  '1251',
     *                                  'cp1252',
     *                                  'windows-1252',
     *                                  '1252',
     *                                  'koi8-r',
     *                                  'koi8-ru',
     *                                  'koi8r',
     *                                  'big5',
     *                                  '950',
     *                                  'gb2312',
     *                                  '936',
     *                                  'big5-hkscs',
     *                                  'shift_jis',
     *                                  'sjis',
     *                                  'sjis-win',
     *                                  'cp932',
     *                                  '932',
     *                                  'euc-jp',
     *                                  'eucjp',
     *                                  'eucjp-win',
     *                                  'macroman'
     *                                  
     * @param array $data_vars_2_html_escape An array of keys in $data and $this->data whose values (only strings) will be 
     *                                 individually escaped using Zend\Escaper\Escaper::escapeHtml($string). 
     *                                 Set this for keys in $data and $this->data with values (only strings) like html tags 
     *                                 and the likes (anything you would normally escape via htmlspecialchars).
     * 
     * @param array $data_vars_2_html_attr_escape An array of keys in $data and $this->data whose values (only strings) will be 
     *                                      individually escaped using Zend\Escaper\Escaper::escapeHtmlAttr($string). 
     *                                      Set this for keys in $data and $this->data with values (only strings) that will be rendered 
     *                                      as attributes within html tags.
     * 
     * @param array $data_vars_2_css_escape An array of keys in $data and $this->data whose values (only strings) will be 
     *                                individually escaped using Zend\Escaper\Escaper::escapeCss($string). 
     *                                Set this for keys in $data and $this->data with values (only strings) that will be 
     *                                rendered inside css style tags or inside the style attribute of any 
     *                                html element. CSS escaping via Zend\Escaper\Escaper::escapeCss($string) 
     *                                excludes only basic alphanumeric characters and escapes all other 
     *                                characters into valid CSS hexadecimal escapes.
     * 
     * @param array $data_vars_2_js_escape An array of keys in $data and $this->data whose values (only strings) will be 
     *                               individually escaped using Zend\Escaper\Escaper::escapeJs($string).
     *                               Set this for keys in $data and $this->data with values (only strings) that will be 
     *                               rendered as Javascript data values (eg. Javascript string literals). 
     *                               Javascript escaping via Zend\Escaper\Escaper::escapeJs($string) applies 
     *                               to all literal strings and digits. It is not possible to safely escape 
     *                               other Javascript markup.
     * 
     * @return string the output that is generated when $file_name or $this->file_name is included
     * 
     * @throws \Rotexsoft\FileRenderer\FileNotFoundException
     * 
     */
    public function renderToString(
        $file_name='',
        array $data = array(),
        $escape_encoding = 'utf-8',
        array $data_vars_2_html_escape = array(),
        array $data_vars_2_html_attr_escape = array(),
        array $data_vars_2_css_escape = array(),
        array $data_vars_2_js_escape = array() 
    ) {
        if( !is_string($file_name) ) {
            
            $msg = "ERROR: ". get_class($this) ."::".__FUNCTION__."(..) expects"
                 . " first parameter (the name of the php file to be rendered) to be a `string`." 
                 . PHP_EOL .'`'. $this->getVarType($file_name, true).'` was supplied with the value below:'
                 . PHP_EOL . var_export($file_name, true). PHP_EOL ;
            
            throw new \InvalidArgumentException($msg);
        }
        
        $located_file = $this->locateFile($file_name);
        
        if( $located_file === false ) {
            
            //File name supplied to this method was not found. 
            //Try to see if the file name supplied when this 
            //class was instantiated can be found.
            $located_file = $this->locateFile($this->file_name);
        }
        
        if( $located_file === false ) {
            
            //The file does not exist in any of the registered possible paths
            $msg = "ERROR: Could not load the file named `$file_name` "
                . "from any of the paths below:"
                . PHP_EOL . implode(PHP_EOL, $this->file_paths) . PHP_EOL
                . PHP_EOL . get_class($this) . '::' . __FUNCTION__ . '(...).' 
                . PHP_EOL;
            
            throw new FileNotFoundException($msg);
        }
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        ////
        //// Deliberately not specifying parameters in the anonymous function's 
        //// definition signature below in order to avoid having any explicit 
        //// variable(s) defined inside the anonymous function. Rather, the 
        //// parameters are being accessed via func_get_arg() and not even 
        //// assigned to any local variable(s) inside the function.
        ////
        //// This way we need not worry about any variable(s) being overwritten
        //// inside the anonymous function when extract(..) is called within the 
        //// anonymous function.
        ////  
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        $render_view = function()
        {
            //func_get_arg(0): the name of the file to be included whose output 
            //                 is to be captured and returned

            //func_get_arg(1): the data array from which to extract variables

            //Extract variables from the data array which may be needed in the
            //view file to be included below.
            extract(func_get_arg(1));

            // Capture the view output
            ob_start();

            // Load the view within the current scope
            include func_get_arg(0);

            // Get the captured output and close the buffer
            return ob_get_clean();
        };
        
        $merged_data = array_merge($this->data, $data);

        //escape data
        $this->escapeData(
                    $merged_data, 
                    $escape_encoding, 
                    array_merge($this->data_vars_2_html_escape, $data_vars_2_html_escape), 
                    array_merge($this->data_vars_2_html_attr_escape, $data_vars_2_html_attr_escape), 
                    array_merge($this->data_vars_2_css_escape, $data_vars_2_css_escape), 
                    array_merge($this->data_vars_2_js_escape, $data_vars_2_js_escape)
                );
        
        return $render_view($located_file, $merged_data);
    }
    
    /**
     * 
     * Captures and prints out the output that is generated when a php file is included. 
     * 
     * @param string $file_name Name of php file to be included (with/without
     *                          the directory path). If the directory path is 
     *                          included & the file exists, it is included and
     *                          the output is returned. If the directory path is 
     *                          not included, the file will be searched for from 
     *                          the list of directories registered in 
     *                          $this->file_paths.
     *                          
     *                          If $file_name still can't be found or is an empty 
     *                          string or is not supplied, this method tries to 
     *                          locate $this->file_name if possible and renders 
     *                          it instead.
     *                          
     * @param array $data Array of data to be extracted to make local variables.
     *                    It is combined together with $this->data. It will
     *                    overwrite item(s) with the same key in $this->data.
     * 
     * @param string $escape_encoding Encoding to be used for escaping data values in $data and $this->data.
     *                                Below is a list of supported encodings:  
     *                                  'iso-8859-1',
     *                                  'iso8859-1',
     *                                  'iso-8859-5',
     *                                  'iso8859-5',
     *                                  'iso-8859-15',
     *                                  'iso8859-15',
     *                                  'utf-8',
     *                                  'cp866',
     *                                  'ibm866',
     *                                  '866',
     *                                  'cp1251',
     *                                  'windows-1251',
     *                                  'win-1251',
     *                                  '1251',
     *                                  'cp1252',
     *                                  'windows-1252',
     *                                  '1252',
     *                                  'koi8-r',
     *                                  'koi8-ru',
     *                                  'koi8r',
     *                                  'big5',
     *                                  '950',
     *                                  'gb2312',
     *                                  '936',
     *                                  'big5-hkscs',
     *                                  'shift_jis',
     *                                  'sjis',
     *                                  'sjis-win',
     *                                  'cp932',
     *                                  '932',
     *                                  'euc-jp',
     *                                  'eucjp',
     *                                  'eucjp-win',
     *                                  'macroman'
     *                                  
     * @param array $data_vars_2_html_escape An array of keys in $data and $this->data whose values (only strings) will be 
     *                                 individually escaped using Zend\Escaper\Escaper::escapeHtml($string). 
     *                                 Set this for keys in $data and $this->data with values (only strings) like html tags 
     *                                 and the likes (anything you would normally escape via htmlspecialchars).
     * 
     * @param array $data_vars_2_html_attr_escape An array of keys in $data and $this->data whose values (only strings) will be 
     *                                      individually escaped using Zend\Escaper\Escaper::escapeHtmlAttr($string). 
     *                                      Set this for keys in $data and $this->data with values (only strings) that will be rendered 
     *                                      as attributes within html tags.
     * 
     * @param array $data_vars_2_css_escape An array of keys in $data and $this->data whose values (only strings) will be 
     *                                individually escaped using Zend\Escaper\Escaper::escapeCss($string). 
     *                                Set this for keys in $data and $this->data with values (only strings) that will be 
     *                                rendered inside css style tags or inside the style attribute of any 
     *                                html element. CSS escaping via Zend\Escaper\Escaper::escapeCss($string) 
     *                                excludes only basic alphanumeric characters and escapes all other 
     *                                characters into valid CSS hexadecimal escapes.
     * 
     * @param array $data_vars_2_js_escape An array of keys in $data and $this->data whose values (only strings) will be 
     *                               individually escaped using Zend\Escaper\Escaper::escapeJs($string).
     *                               Set this for keys in $data and $this->data with values (only strings) that will be 
     *                               rendered as Javascript data values (eg. Javascript string literals). 
     *                               Javascript escaping via Zend\Escaper\Escaper::escapeJs($string) applies 
     *                               to all literal strings and digits. It is not possible to safely escape 
     *                               other Javascript markup.
     * 
     * @return void
     * 
     * @throws \Rotexsoft\FileRenderer\FileNotFoundException
     * 
     */
    public function renderToScreen(
        $file_name='', 
        array $data = array(),
        $escape_encoding = 'utf-8',
        array $data_vars_2_html_escape = array(),
        array $data_vars_2_html_attr_escape = array(),
        array $data_vars_2_css_escape = array(),
        array $data_vars_2_js_escape = array()
    ) {
        echo $this->renderToString(
                        $file_name, 
                        $data, 
                        $escape_encoding, 
                        $data_vars_2_html_escape, 
                        $data_vars_2_html_attr_escape, 
                        $data_vars_2_css_escape, 
                        $data_vars_2_js_escape
                    );
    }
    
    /**
     * 
     * Escapes values in an array and all its sub-arrays. 
     *                          
     * @param array $data Array of data to be escaped.
     * 
     * @param string $escape_encoding Encoding to be used for escaping data values in $data and $this->data.
     *                                Below is a list of supported encodings:  
     *                                  'iso-8859-1',
     *                                  'iso8859-1',
     *                                  'iso-8859-5',
     *                                  'iso8859-5',
     *                                  'iso-8859-15',
     *                                  'iso8859-15',
     *                                  'utf-8',
     *                                  'cp866',
     *                                  'ibm866',
     *                                  '866',
     *                                  'cp1251',
     *                                  'windows-1251',
     *                                  'win-1251',
     *                                  '1251',
     *                                  'cp1252',
     *                                  'windows-1252',
     *                                  '1252',
     *                                  'koi8-r',
     *                                  'koi8-ru',
     *                                  'koi8r',
     *                                  'big5',
     *                                  '950',
     *                                  'gb2312',
     *                                  '936',
     *                                  'big5-hkscs',
     *                                  'shift_jis',
     *                                  'sjis',
     *                                  'sjis-win',
     *                                  'cp932',
     *                                  '932',
     *                                  'euc-jp',
     *                                  'eucjp',
     *                                  'eucjp-win',
     *                                  'macroman'
     *                                  
     * @param array $data_vars_2_html_escape An array of keys in $data and $this->data whose values (only strings) will be 
     *                                 individually escaped using Zend\Escaper\Escaper::escapeHtml($string). 
     *                                 Set this for keys in $data and $this->data with values (only strings) like html tags 
     *                                 and the likes (anything you would normally escape via htmlspecialchars).
     * 
     * @param array $data_vars_2_html_attr_escape An array of keys in $data and $this->data whose values (only strings) will be 
     *                                      individually escaped using Zend\Escaper\Escaper::escapeHtmlAttr($string). 
     *                                      Set this for keys in $data and $this->data with values (only strings) that will be rendered 
     *                                      as attributes within html tags.
     * 
     * @param array $data_vars_2_css_escape An array of keys in $data and $this->data whose values (only strings) will be 
     *                                individually escaped using Zend\Escaper\Escaper::escapeCss($string). 
     *                                Set this for keys in $data and $this->data with values (only strings) that will be 
     *                                rendered inside css style tags or inside the style attribute of any 
     *                                html element. CSS escaping via Zend\Escaper\Escaper::escapeCss($string) 
     *                                excludes only basic alphanumeric characters and escapes all other 
     *                                characters into valid CSS hexadecimal escapes.
     * 
     * @param array $data_vars_2_js_escape An array of keys in $data and $this->data whose values (only strings) will be 
     *                               individually escaped using Zend\Escaper\Escaper::escapeJs($string).
     *                               Set this for keys in $data and $this->data with values (only strings) that will be 
     *                               rendered as Javascript data values (eg. Javascript string literals). 
     *                               Javascript escaping via Zend\Escaper\Escaper::escapeJs($string) applies 
     *                               to all literal strings and digits. It is not possible to safely escape 
     *                               other Javascript markup.
     * 
     * @param \Zend\Escaper\Escaper $escaper An optional escaper object that will be used for escaping. 
     * 
     * @return void
     * 
     * @throws \Rotexsoft\FileRenderer\FileNotFoundException
     * 
     */
    protected function escapeData(
        array &$data,
        $escape_encoding = 'utf-8',
        array $data_vars_2_html_escape = array(),
        array $data_vars_2_html_attr_escape = array(),
        array $data_vars_2_css_escape = array(),
        array $data_vars_2_js_escape = array(),
        \Zend\Escaper\Escaper $escaper = null
    ) {
        if ( count($data) <= 0 ) {
            
            //no data supplied; nothing to do
            return;
            
        } else if(
            count($data_vars_2_html_escape) <= 0
            && count($data_vars_2_html_attr_escape) <= 0
            && count($data_vars_2_css_escape) <= 0
            && count($data_vars_2_js_escape) <= 0
        ) {
            //no field has been specified for escaping; nothing to do
            return;
        }
        
        $hash_of_data_array = spl_object_hash(json_decode(json_encode($data)));
        
        if( 
            array_key_exists($hash_of_data_array, $this->multi_escape_prevention_guard) 
            && $this->multi_escape_prevention_guard[$hash_of_data_array]['escape_encoding'] === $escape_encoding
            && $this->multi_escape_prevention_guard[$hash_of_data_array]['data_vars_2_html_escape'] === $data_vars_2_html_escape
            && $this->multi_escape_prevention_guard[$hash_of_data_array]['data_vars_2_html_attr_escape'] === $data_vars_2_html_attr_escape
            && $this->multi_escape_prevention_guard[$hash_of_data_array]['data_vars_2_css_escape'] === $data_vars_2_css_escape
            && $this->multi_escape_prevention_guard[$hash_of_data_array]['data_vars_2_js_escape'] === $data_vars_2_js_escape
        ) {
            //the data array has already been escaped; don't wanna escape already escaped data
            return;
        }
        
        $final_encoding = (empty($escape_encoding)) 
                            ? ((empty($this->escape_encoding))? 'utf-8' : $this->escape_encoding) 
                            : $escape_encoding;
        
        if( is_null($escaper) ) { $escaper = new \Zend\Escaper\Escaper($final_encoding); }
        
        foreach( $data as $key => $value ) {
         
            $methods = array();
            
            if(in_array($key, $data_vars_2_html_escape) || in_array('*', $data_vars_2_html_escape)) { $methods[] = 'escapeHtml'; }
            
            if(in_array($key, $data_vars_2_html_attr_escape) || in_array('*', $data_vars_2_html_attr_escape)) { $methods[] = 'escapeHtmlAttr'; }
            
            if(in_array($key, $data_vars_2_css_escape) || in_array('*', $data_vars_2_css_escape)) { $methods[] = 'escapeCss'; }
            
            if(in_array($key, $data_vars_2_js_escape) || in_array('*', $data_vars_2_js_escape)) { $methods[] = 'escapeJs'; }
            
            if( count($methods) > 0 || is_array($data[$key]) ) {
                
                if( is_array($data[$key]) ) {
                    
                    // recursively escape sub-array
                    $this->escapeData(
                                $data[$key], 
                                $final_encoding, 
                                $data_vars_2_html_escape, 
                                $data_vars_2_html_attr_escape, 
                                $data_vars_2_css_escape, 
                                $data_vars_2_js_escape,
                                $escaper // pass already instantiated escaper
                            );
                    
                } else if( is_string($data[$key]) ) {
                    
                    foreach($methods as $method) {
                        
                        // escape the value
                        $data[$key] = $escaper->$method($data[$key]);
                    }
                    
                } //if( is_array($data[$key]) ) ... else if( is_string($data[$key]) )
            } // if( count($methods) > 0 || is_array($data[$key]) )
        } // foreach( $data as $key => $value )
        
        //add the hash of the data array we have just escaped to the list of
        //hashes of escaped data arrays
        $hash_of_escaped_data_array = spl_object_hash(json_decode(json_encode($data)));
        
        $this->multi_escape_prevention_guard[$hash_of_escaped_data_array] = array(
            'escape_encoding'=>$escape_encoding,
            'data_vars_2_html_escape'=>$data_vars_2_html_escape,
            'data_vars_2_html_attr_escape'=>$data_vars_2_html_attr_escape,
            'data_vars_2_css_escape'=>$data_vars_2_css_escape,
            'data_vars_2_js_escape'=>$data_vars_2_js_escape
        );
    }
    
    /**
     * 
     * @param string $file_name Name of file (with / without the directory path) 
     *                          to be located in the registered possible paths 
     *                          ($this->file_paths).
     * 
     * @return boolean|string If the directory path is included in $file_name 
     *                        & the file exists, this method returns $file_name.
     * 
     *                        Else it searches for $file_name within the 
     *                        directories registered in $this->file_paths and 
     *                        returns $file_name prepended with the first directory 
     *                        name in $this->file_paths in which $file_name exists.
     * 
     *                        Finally, if $file_name cannot be found in any of 
     *                        the registered paths, false is returned.
     * 
     * @throws \InvalidArgumentException
     * 
     */
    public function locateFile($file_name) {
        
        if( !is_string($file_name) ) {
                        
            $msg = "ERROR: ". get_class($this) ."::".__FUNCTION__."(...) expects first parameter (the name of the php file to be located) to be a `string`." 
                   . PHP_EOL .'`'. $this->getVarType($file_name, true).'` was supplied with the value below:'
                   . PHP_EOL . var_export($file_name, true). PHP_EOL ;
            
            throw new \InvalidArgumentException($msg);
        }
        
        $ds = DIRECTORY_SEPARATOR;
        
        //Check to see if a path was prepended to the file name
        $file_name_contains_path = strlen( basename($file_name) ) < strlen($file_name);

        //Check if file actually exists as is (if a path was prepended to it).
        $located_file = 
            ( 
                !empty($file_name) && file_exists($file_name) 
                && is_file($file_name) && $file_name_contains_path 
            ) ? $file_name : false;
        
        if(  $located_file === false && !empty($file_name) ) {
            
            //$file_name is not an existent file on its own. 
            //Search for it in the list of paths registered in $this->file_paths.
            foreach ( $this->file_paths as $possible_path ) {

                $potential_file =  
                    $this->normalizeFolderPath($possible_path) . $ds . $file_name;

                if( file_exists($potential_file) && is_file($potential_file) ) {

                    //found the file
                    $located_file = $potential_file;
                    break;
                }
            }
        }
        
        //file not found
        return $located_file;
    }
    
    /**
     * 
     * Trims off the right-most character at the end of the string `$file_path` 
     * if it is a directory separator charater (ie. '\' or '/').
     * 
     * @param string $file_path
     * 
     * @return string `$file_path` as is if right-most character at the end of the 
     *                string is not a directory separator charater (ie. '\' or '/').
     *                If the right-most character at the end of the string `$file_path`
     *                is a directory separator charater (ie. '\' or '/'), return
     *                `$file_path` without the right-most directory separator charater
     *                at the end.
     */
    protected function normalizeFolderPath($file_path) {

        //trim right-most linux style path separator if any
        $trimed_path = rtrim($file_path, '/');

        if( strlen($trimed_path) === strlen($file_path) ) {

            //there was no right-most linux path separator
            //try to trim right-most windows style path separator if any
            $trimed_path = rtrim($trimed_path, '\\');
        }

        return $trimed_path;
    }
    
    /**
     * 
     * An enhancement to PHP's gettype function that displays the class name of a variable if the variable is an object.
     * 
     * @param mixed $var a variable whose type is to be determined
     * @param bool $cap_first flag to indicate if the variable's type should be returned with the first letter in uppercase
     * 
     * @return string the variable's type
     * 
     */
    protected function getVarType($var, $cap_first=false) {

        if( is_object($var) ) { return $cap_first ? ucfirst(get_class($var)) : get_class($var); }

        return $cap_first ? ucfirst(gettype($var)) : gettype($var);
    }
}

class FileNotFoundException extends \Exception { }