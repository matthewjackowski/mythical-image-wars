<?php
/*
 * Class Name: Mythical Image Wars Debug
 * Description: An plugin/application error/trace log handler.  It *only* traps info from the plugin, and the display is controllable from "Settings" page
 * Author: Matthew Jackowski
 * Version: 0.2.0
 * Author URI: http://www.linkedin.com/pub/matthew-jackowski/6/6b2/242
 * License: GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html
 */
?>
<?php
class mythicalImageWarsDebug 
{ 
    private static $calls; //array of debug output messages
    static function &init() {
        mythicalImageWarsDebug::logTrace();
        // Do static initializations here
        static $instance = array();
        if ( !$instance ) {
            $instance[0] =& new mythicalImageWarsDebug;
                }
                return $instance[0];
    }
    function mythicalImageWarsDebug() {
        mythicalImageWarsDebug::logTrace();
        // Check to see if plugin is in debug mode
        // If not, skip all display handlers and custom error handling
        if (mythicalImageWars::getDebugMode ()) {
            set_error_handler(array ('mythicalImageWarsDebug','logError'));
            // Check for admin level if not surpress all debug output hooks
            if ( current_user_can('manage_options')){
                add_action('wp_footer',array('mythicalImageWarsDebug','printLog'));
                add_action('admin_footer',array('mythicalImageWarsDebug','printLog'));
                    } // End if Wordpress user admin check
                } // End if debug mode check
    }
    public static function logTrace($message = null){ 
        if(!is_array(self::$calls)) 
            self::$calls = array(); 
        $call = debug_backtrace(false); 
        $call = (isset($call[1]))?$call[1]:$call[0]; 
        $call['message'] = $message; 
        array_push(self::$calls, $call); 
    }
    public static function logError ($severity, $message, $filename, $lineno ){
        if(!is_array(self::$calls)) 
            self::$calls = array();
        if (strpos($filename,'mythic-image-wars')) {
        $call = debug_backtrace(false); 
        $call = (isset($call[2]))?$call[2]:$call[1]; 
        $call['message'] = 'File: '.basename($filename) .' Line: '.$lineno. ': '. $message;
        array_push(self::$calls, $call);
        }
    }
    public function printLog () {
            echo ('<div id="miw_debug" class="transparent" style="width:90%;margin: 1em auto;padding: 10px 160px;text-align: left;z-index: 999;">'."\n");
            echo ('<h3>Plugin: Mythic Image Wars Debug Mode Output</h3>'."\n");
            array_walk( mythicalImageWarsDebug::$calls, array('mythicalImageWarsDebug','printLogCallback'));
            echo "</div>";
    }
    function printLogCallback($value,$key) {
  //          $options = get_option('debug_section');
            echo "*<br/>";
            if (array_key_exists('file',$value))
                    echo ("<b>File: ". basename($value['file']). "</b> - ");
            if (array_key_exists('line',$value))
                     echo ('<font color="green">Line #: '. $value['line'].'</font>');
            echo "<br/>";
            if (array_key_exists('class',$value))
                    echo ("<b>Class: ". $value['class']. "</b> - ");
            if (array_key_exists('function',$value))
                     echo ('<font color="green">Function: '. $value['function'].'</font>');
            echo "<br/>";
            if (array_key_exists('type',$value)) {
                echo ("<b>Type: ");
                switch ($value['type']) {
                  case "::":
                    echo ("static method call");
                    break;
                  case "->" :
                      echo ("method call");
                      break;
                  default :
                      echo ("function call");
                }
                 echo("</b> - ");
            }
            if (array_key_exists('args',$value)) {
                     echo ('<font color="green">Parameters: ');
                    print_r($value['args']);
                    echo ('</font>');
            }
            echo "<br/>";
            if (array_key_exists('message',$value)&& $value['message']!=null)
                    echo ('<font color="red">');
                    print_r ($value['message']);
                    echo ('</font>'); 
            echo "<br/>*";
        }
    
}
?>