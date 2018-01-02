<?php
require_once("SiteRestHandler.php");

$view = "";
if(isset($_GET["view"]))
    $view = $_GET["view"];


/*
 * RESTful service 控制器
 * URL 映射
*/
switch($view){
    case "login_out":
        // 处理 REST Url /site/list/
        $siteRestHandler = new SiteRestHandler();
        $usrId = $_POST["id"];
        $siteRestHandler->login_out($usrId);
    break;
    case "action_controler":
      // 处理 REST Url /site/list/
      $siteRestHandler = new SiteRestHandler();

      $siteRestHandler->action_controler();
    break;
    case "login":
      // 处理 REST Url /site/list/
      $siteRestHandler = new SiteRestHandler();
      $para = array(
        'name' =>$_GET["name"],
        'password' =>$_GET["password"],
        );
      $siteRestHandler->login($para);
      break;

    case "all":
        // 处理 REST Url /site/list/
        $siteRestHandler = new SiteRestHandler();
        $siteRestHandler->getAllSites();
        break;

    case "single":
        // 处理 REST Url /site/show/<id>/
        $siteRestHandler = new SiteRestHandler();
        $siteRestHandler->getSite($_GET["id"]);
        break;

    case "add_user" :
          $siteRestHandler = new SiteRestHandler();
          $para = array(
            'name' =>$_GET["name"],
            'sex' =>$_GET["sex"],
            'age' =>$_GET["age"],
            'phone' =>$_GET["phone"],
            'address' =>$_GET["address"],
            'password' =>$_GET["password"],
            );

         $siteRestHandler->addUser($para);
         break;
    case "edit_user":
        $siteRestHandler = new SiteRestHandler();
        $para = array(
          'id' => $_GET["id"],
          'name' =>$_GET["name"],
          'sex' =>$_GET["sex"],
          'age' =>$_GET["age"],
          'phone' =>$_GET["phone"],
          'address' =>$_GET["address"],
          'password' =>$_GET["password"],
          );

          $siteRestHandler->editUser($para);
           break;
    case  "list_user" :
      //echo 123;
      $page = $_POST["page"];
      $perPage = $_POST["perPage"];


      $siteRestHandler = new SiteRestHandler();
      $siteRestHandler->listUser($page,$perPage);
      break;

    case "del_user":
      $id = $_POST["id"];
      $siteRestHandler = new SiteRestHandler();
      $siteRestHandler->delUser($id);
      break;

}
?>
