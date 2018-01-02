<?php
require_once("SimpleRest.php");
require_once("Site.php");
class User
{
  public  $id;
  public  $name;
  public  $sex;
  public  $age;
  public $phone;
  public $address;
  public $password;
}
class Result
{
  public $totalPages;
  public $totalCount;
  public $data;
  public $code = 0;
}

class SiteRestHandler extends SimpleRest {

    var $SessionUid = "SessionUid";

    //构造函数
    function __construct(){

    }

    //连接数据库
    public function connectDB(){

      $servername = "localhost";
      $username = "root";
      $password = "";
      $dbname = "study";

      // 创建连接
      $conn = new mysqli($servername, $username, $password,$dbname);
      mysqli_set_charset($conn, "utf8");

      // 检测连接
      if ($conn->connect_error) {
          die("连接失败: " . $conn->connect_error);
      }

      //echo "连接成功";
      return $conn;
    }

    public function login_out($userId){
      try {
        //echo $userId;
        $expire= time() - 60*60*24*30;//60*60*24*30
        setcookie("userId",$userId, $expire,"/");

          $this->SessionUid.=$userId;//从session里返回信息
              session_start();
          if(isset($_SESSION[$this->SessionUid]))
          {
              unset($_SESSION[$this->SessionUid]);
          }

          echo "success";

      } catch (Exception $e) {
          echo 'Caught exception: ',  $e->getMessage(), "\n";
      }

    }

    public function login($para){
      $name = $para['name'];
      $password = $para['password'];

      $conn = $this->connectDB();
      $sql = "SELECT * FROM users WHERE `name` = '$name' AND `password` = '$password'";

      $result = $conn->query($sql);
      if ($result->num_rows > 0) {

        $row = mysqli_fetch_assoc($result);
        $userId = $row['id'];
        $expire= time()+ 60*60*24*30;//60*60*24*30
        setcookie("userId",$userId, $expire,"/");

        session_start();

        $this->SessionUid .=  $userId;//session uid


        $_SESSION[$this->SessionUid]= $this->encodeJson($row);//登陆人信息存入session
        echo  $_SESSION[$this->SessionUid];//从session里返回信息


      }else{
        echo "没有当前用户";
      }
      $conn->close();

    }

    public function action_controler(){
        if(isset($_COOKIE["userId"])){
          $userId = $_COOKIE["userId"];
           $this->SessionUid.=$userId;//从session里返回信息
              session_start();
            //  $_SESSION[$this->SessionUid]['view'] = 1;
              echo  $_SESSION[$this->SessionUid];//从session里返回信息

        }else{
          echo "没有当前用户";
        }

    }

    public function addUser($para){
      $name = $para['name'];
      $sex = $para['sex'];
      $age = $para['age'];
      $phone = $para['phone'];
      $address = $para['address'];
      $password = $para['password'];

      $conn = $this->connectDB();
      $sql = "INSERT INTO users (id, name, sex,age,phone,address,password) VALUES ('','". $name ."',' ".$sex."','".$age."','".$phone."','".$address."','".$password."')";
      if ($conn->query($sql) === TRUE) {
        echo "新记录插入成功";
      } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
      }

      $conn->close();
    }
    public function editUser($para){
      $id = $para['id'];
      $name = $para['name'];
      $sex = $para['sex'];
      $age = $para['age'];
      $phone = $para['phone'];
      $address = $para['address'];
      $password = $para['password'];

      $conn = $this->connectDB();
      $sql = "UPDATE  users  SET  name = '$name', sex = '$sex',age = '$age',phone = '$phone',address = '$address',password = '$password' WHERE id = $id";
      if ($conn->query($sql) === TRUE) {
        echo "更新记录成功";
      } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
      }

      $conn->close();
    }
    public function listUser($page,$perPage){

      $conn = $this->connectDB();

      $start = $page * $perPage - $perPage;
      $end = $page * $perPage;

      $sqlCount = "SELECT COUNT(*) AS `total` FROM users";
      $count = $conn->query($sqlCount);

      $result = new Result();
      $result->totalCount = (int) mysqli_fetch_array($count)["total"];
      $result->totalPages = ceil($result->totalCount / $perPage);

      $sql = "SELECT * FROM users limit  $start,$end";
      $resultData = $conn->query($sql);
      $data =array(); //定义好一个数组.PHP中array相当于一个数据字典.
      if ($resultData->num_rows > 0) {
        // 输出数据
        while($row = $resultData->fetch_assoc()) {
          $user =new User();
          $user->id = $row["id"];
          $user->name = $row["name"];
          $user->sex = $row["sex"];
          $user->age = $row["age"];
            $user-> phone = $row["phone"];
            $user-> address = $row["address"];
              $user-> password = $row["password"];
            //  array_push($data,$user);
              $data[] = $user;
          //echo "id: " . $row["id"]. " - Name: " . $row["name"]. " " . $row["age"]. "<br>";
        }
        $result->data = $data;
        echo $this->encodeJson($result);

      } else {
          echo "0 结果";
      }

      $conn->close();
    }
    public function delUser($id){

      $conn = $this->connectDB();
      $sql = "DELETE  FROM users WHERE id = " . $id;
      $conn->query($sql);
      if (mysqli_connect_errno())
      {
          echo "连接失败: " . mysqli_connect_error();
      }else{
        echo "删除成功!";
      }

    }

    function getAllSites() {

        $site = new Site();
        $rawData = $site->getAllSite();

        if(empty($rawData)) {
            $statusCode = 404;
            $rawData = array('error' => 'No sites found!');
        } else {
            $statusCode = 200;
        }

        $requestContentType = $_SERVER['HTTP_ACCEPT'];

        $this ->setHttpHeaders($requestContentType, $statusCode);

        if(strpos($requestContentType,'application/json') !== false){
            $response = $this->encodeJson($rawData);
            echo $response;
        } else if(strpos($requestContentType,'text/html') !== false){
            $response = $this->encodeHtml($rawData);
            echo $response;
        } else if(strpos($requestContentType,'application/xml') !== false){
            $response = $this->encodeXml($rawData);
            echo $response;
        }
    }

    public function encodeHtml($responseData) {

        $htmlResponse = "<table border='1'>";
        foreach($responseData as $key=>$value) {
                $htmlResponse .= "<tr><td>". $key. "</td><td>". $value. "</td></tr>";
        }
        $htmlResponse .= "</table>";
        return $htmlResponse;
    }

    public function encodeJson($responseData) {
        $jsonResponse = json_encode($responseData);
        return $jsonResponse;
    }

    public function encodeXml($responseData) {
        // 创建 SimpleXMLElement 对象
        $xml = new SimpleXMLElement('<?xml version="1.0"?><site></site>');
        foreach($responseData as $key=>$value) {
            $xml->addChild($key, $value);
        }
        return $xml->asXML();
    }

    public function getSite($id) {

        $site = new Site();
        $rawData = $site->getSite($id);

        if(empty($rawData)) {
            $statusCode = 404;
            $rawData = array('error' => 'No sites found!');
        } else {
            $statusCode = 200;
        }

        $requestContentType = $_SERVER['HTTP_ACCEPT'];
        $this ->setHttpHeaders($requestContentType, $statusCode);

        if(strpos($requestContentType,'application/json') !== false){
            $response = $this->encodeJson($rawData);
            echo $response;
        } else if(strpos($requestContentType,'text/html') !== false){
            $response = $this->encodeHtml($rawData);
            echo $response;
        } else if(strpos($requestContentType,'application/xml') !== false){
            $response = $this->encodeXml($rawData);
            echo $response;
        }
    }
}
?>
