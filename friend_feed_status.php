<?php
/*
Plugin Name: FriendFeed Status Updater
Plugin URI: http://dotblog.ru
Description: Обновление статуса FriendFeed
Version: 0.1 бета
Author: Saint_Byte
Author URI: http://dotblog.ru/
*/
function post_it($datastream,$headers, $url) {

$url = preg_replace("@^http://@i", "", $url);
$host = substr($url, 0, strpos($url, "/"));
$uri = strstr($url, "/");

      $reqbody = "";
      foreach($datastream as $key=>$val) {
          if (!empty($reqbody)) $reqbody.= "&";
      $reqbody.= $key."=".urlencode($val);
      }

      foreach($headers as $key=>$val) {
          if (!empty($headers)) $hbody.= $key.': '.$val."\r\n";
      }

$contentlength = strlen($reqbody);
     $reqheader =  "POST $uri HTTP/1.1\r\n".
                   "Host: $host\r\n". "User-Agent: PostIt\r\n".
     "Content-Type: application/x-www-form-urlencoded\r\n".
     "Content-Length: $contentlength\r\n".$hbody."\r\n".
     "$reqbody\r\n";
$socket = fsockopen($host, 80, $errno, $errstr);

if (!$socket) {
   $result["errno"] = $errno;
   $result["errstr"] = $errstr;
   return $result;
}

fputs($socket, $reqheader);

while (!feof($socket)) {
   $result[] = fgets($socket, 4096);
}

fclose($socket);

return implode('',$result);
}


function ffeed_options_page()
{
?>
<div class="wrap">
        <h2>Настройки FriendFeed Status Updater</h2>
        <p>Настройки смотрите здесь: <a href="https://friendfeed.com/account/api">https://friendfeed.com/account/api</a>)</p>
        <?php
        if($_POST['ffeed_username'] or $_POST['ffeed_password']) {
                // set the post formatting options
                update_option('ffeed_username', $_POST['ffeed_username']);
                update_option('ffeed_password', $_POST['ffeed_password']);
                echo '<div class="updated"><p>Настройки обновлены.</p></div>';
        }
        ?>

        <form method="post">
        <fieldset class="options">
                        <?php
                        $ffeed_username = get_option('ffeed_username');
                        $ffeed_password = get_option('ffeed_password');
                        ?>
                        <table>
                        <tr>
                        <td>
                        Прозвище на FriendFeed:
                        </td>
                        <td>
                        <input type="text" name="ffeed_username" value="<?php print $ffeed_username; ?>" />
                        </td>
                        </tr>
                        <tr>
                        <td>
                        Удаленный ключ FriendFeed:
                        </td>
                        <td>
                        <input type="text" name="ffeed_password" value="<?php print $ffeed_password; ?>" />
                        </td>
                        </tr>
                        <tr>
                        <td>

                        </td>
                        <td>
                        <input type="submit" value="Сохранить" />
                        </td>
                        </tr>
                        </table>


        </fieldset>
        </form>
</div>
<?php
}
//-----------------------------------------------------------------------------------
function ff_add_menu()
{
        add_options_page('friend_feed_status', 'FF Status Updater', 8, __FILE__, 'ffeed_options_page');
}
add_action('admin_menu', 'ff_add_menu');
//-----------------------------------------------------------------------------------
function send_ff_post($post_ID)  {
   $user = get_option('ffeed_username');
   $password = get_option('ffeed_password');
   if (!empty($user) or !empty($password)) {
           $post = &get_post($post_ID,ARRAY_A);
           $title = $post['post_title'];
           $href  = $post['guid'];
           $ff_post = $title.' '.$href;
           $pass = 'Basic '.base64_encode($user.':'.$password);
           $ddd = post_it(array('body'=>$ff_post),array('Authorization' => $pass), 'http://friendfeed-api.com/v2/entry');
           return $post_ID;
   } else {
           return $post_ID;
   }
}

add_action('publish_post', 'send_ff_post');
