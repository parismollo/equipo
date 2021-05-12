<!-- User table related functions -->
<?php
  require_once("connection.php"); require_once("../login/func_display.php");// Might be redudant if this is already in signup file
  $server = "localhost"; // change according to your settings
  $user = "root";
  $password = "voiture93";
  $database = "Equipo";

  function insert_user_query(&$user_input, $connection){
    foreach ($user_input as $key => $value) {
      $user_input[$key] =  mysqli_real_escape_string($connection, $user_input[$key]);
    }
    $hash_pass = password_hash($user_input["password"], PASSWORD_DEFAULT);
    $pseudo = $user_input["pseudo"];
    $email = $user_input["email"];
    $gender = $user_input["gender"];
    $date = $user_input["date"];
    $query = "INSERT INTO users VALUES ('$pseudo', '$email', '$gender', '$date', '$hash_pass');";
    return $query;

  }

  function login_query(&$user_input, $connection){
    foreach ($user_input as $key => $value) {
      $user_input[$key] = mysqli_real_escape_string($connection, $user_input[$key]);
    }
    $nickname = $user_input["pseudo"];
    $query = "SELECT * FROM users WHERE pseudo = '$nickname' LIMIT 1;";
    return $query;
  }

  function email_query(&$user_input, $connection){
    foreach ($user_input as $key => $value) {
      $user_input[$key] = mysqli_real_escape_string($connection, $user_input[$key]);
    }
    $email = $user_input["email"];
    $query = "SELECT * FROM users WHERE email = '$email' LIMIT 1;";
    return $query;
  }

  function save_user($user_input){
    global $server, $user, $password, $database;
    // 1. connection 2.generate insert query 3. insert user and handle return.
    $connection = connect_to_db($server, $user, $password, $database);
    if(isset($connection)){
      $query = insert_user_query($user_input, $connection);
      $res = mysqli_query($connection, $query);
      $check_pseudo_res = mysqli_query($connection, login_query($user_input, $connection));
      $available_pseudo = (mysqli_num_rows($check_pseudo_res) == 0);
      $check_email_res = mysqli_query($connection, email_query($user_input, $connection));
      $available_email = (mysqli_num_rows($check_email_res) == 0);

      if(!$res){
        $error = mysqli_error($connection);
        if(!$available_pseudo){
          signup_form($errors, "This pseudo has been taken. Try something else!");
          exit;
        }
        if(!$available_email){
          signup_form($errors, "This email has been taken. Try something else!");
          exit;
        }
        display_error_page($error, "signup.php");
        exit;
      }else {
        header('Location: ../login/login.php');
      }
    }else{
      display_error_page("Connection failed", "signup.php");
      exit;
    }
    mysqli_close($connection);
  }

  function get_user($connection, $query){
    $res = mysqli_query($connection, $query);
    return $res;
  }


  function set_user_session($user){
    $_SESSION['user'] = $user;
  }

  function reset_user_session(){
    if(isset($_SESSION['user'])){
      $_SESSION = array();
      session_destroy();
      display_bye_page();
    }else{
      header('Location: login.php');
    }
  }

  function login_user($user_input){
    global $server, $user, $password, $database;
    $connection = connect_to_db($server, $user, $password, $database);
    if (isset($connection)){
      $query = login_query($user_input, $connection);
      $result = get_user($connection, $query);
      $cnt = mysqli_num_rows($result);
      if (!$result || $cnt==0){
        $error = mysqli_error($connection);
        if ($cnt==0){
          login_form($errors, "No users were found with this pseudo!"); // here
          exit;
        }
        display_error_page($error, "login.php");
        exit;
      }else {
        while ($row = mysqli_fetch_assoc($result)){
          if(password_verify($user_input["password"], $row["password"])){
            set_user_session($user_input["pseudo"]);
            display_success_page();
          }else {
            $wrong = "Wrong password!";
            login_form($errors, $wrong);
            break;
          }
        }
      }
    }else{
      display_error_page("Connection failed", "login.php");
      exit;
    }
    mysqli_close($connection);
  }
?>
