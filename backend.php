<?php
if(!isset($_SESSION)){
    session_start();
}
if(isset($_POST['usernameLogin']) && isset($_POST['passwordLogin'])){
    $mysqli = dbconnector(1);

    $username = htmlspecialchars(trim($_POST['usernameLogin']));
    $password = htmlspecialchars($_POST['passwordLogin']);
    $result = $mysqli->query("SELECT * from users where username = '$username'");
    if ($result->num_rows == 1) {
        while($row = $result->fetch_assoc()) {
            if(password_verify($password, $row['hash']) && !empty($row['username'])){
                $_SESSION['ID'] = $row['ID'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['firstName'] = $row['firstName'];
                $_SESSION['lastName'] = $row['lastName'];
                $_SESSION['admin'] = $row['status'];
                session_regenerate_id();
                if($row['status'] == 0){
                    $_SESSION['page'] = "user";
                }else{
                    $_SESSION['page'] = "admin";
                }
            }else{
                echo "Benutzername oder Passwort sind falsch";
            }
            $mysqli->close();
            echo "<meta http-equiv='refresh' content='0;url=index.php'>";
        }
    }
}

function dbconnector($admin){
    $host     = 'localhost';       // host
    $password = '123456';        // Passwort (brauchen Sie nie dieses Passwort)
    $database = 'm151';   // database

    if($admin == 1){
        $username = 'adminM151';
    }else{
        $username = 'userM151';
    }
// Verbindung herstellen
    $mysqli = new mysqli($host, $username, $password, $database);

// Verbindung prüfen
    if ($mysqli->connect_error) {
        die("Verbindung misslungen: " . $mysqli->connect_error);
    }
    return $mysqli;
}
function userPage(){
    return "you are user";
}

function adminPage(){
    $output = '<table class="table">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">ID</th>
      <th scope="col">Username</th>
      <th scope="col">First Name</th>
      <th scope="col">Last Name</th>
      <th scope="col">Categories</th>
      <th scope="col">Admin</th>
    </tr>
  </thead>
  <tbody>';
    $mysqli = dbconnector(1);
    $i = 0;
    $result = $mysqli->query("SELECT * from users;");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $id = $row['ID'];
            $username = $row['username'];
            $firstName = $row['firstName'];
            $lastName = $row['lastName'];
            $status = $row['status'];

            $i++;
            $output .= "<tr>
                          <th scope='row'>#$i</th>
                          <td>$id</td>
                          <td>$username</td>
                          <td>$firstName</td>
                          <td>$lastName</td>
                          <td>WIP</td>
                          <td>$status</td>
                        </tr>";
        }
    }else{
        $output .= "no results";
    }
    $output .= "</tbody></table>";
    $mysqli->close();
    return $output;
}
function login(){
    return '<div class="container">
                <div class="row vertical-offset-100">
                    <div class="col-md-4 col-md-offset-4">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Please sign in</h3>
                            </div>
                            <div class="panel-body">
                                <form accept-charset="UTF-8" role="form" method="post" action="backend.php">
                                <fieldset>
                                    <div class="form-group">
                                        <input class="form-control" placeholder="Username" name="usernameLogin" type="text" required>
                                    </div>
                                    <div class="form-group">
                                        <input class="form-control" placeholder="Password" name="passwordLogin" type="password" value="" required>
                                    </div>
                                    <input class="btn btn-lg btn-success btn-block" type="submit" value="Login">
                                </fieldset>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
}

if(isset($_GET['logout'])){
    logout();
}
function logout(){
    session_destroy();
    echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}

if(isset($_GET['page']) && $_GET['page'] == "default"){
    if(isset($_SESSION['admin']) && $_SESSION['admin'] == 1){
        $_SESSION['page'] = "admin";
    }elseif (isset($_SESSION['admin']) && $_SESSION['admin'] == 0){
        $_SESSION['page'] = "user";
    }else{
        $_SESSION['page'] = "login";
    }
    echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}
if(isset($_GET['page']) && $_GET['page'] == "newUser"){
    $_SESSION['page'] = "newUser";
    echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}
function userForm($username = "", $firstName = "", $lastName = "", $categories = [""], $status = ""){
    $output = "<div class='container'>
    <h1>Registrierung</h1>

    <form action='backend.php' method='post'>
        <!-- benutzername -->
        <div class='form-group'>
            <label for='username'>Username *</label>
            <input type='text' name='username' class='form-control' id='username'
            value='$username'
                   placeholder='Username'
                   maxlength='30' required>
        </div>
        <!-- vorname -->
        <div class='form-group'>
            <label for='firstname'>First Name *</label>
            <input type='text' name='firstname' class='form-control' id='firstname'
            value='$firstName'
                   placeholder='First Name'
                   required>
        </div>
        <!-- nachname -->
        <div class='form-group'>
            <label for='lastname'>Last Name *</label>
            <input type='text' name='lastname' class='form-control' id='lastname'
            value='$lastName'
                   placeholder='Last Name'
                   maxlength='30'
                   required>
        </div>
        <!-- password -->
        <div class='form-group'>
            <label for='password'>Password *</label>
            <input type='password' name='password' class='form-control' id='password'
                   placeholder='Password'
                   required>
        </div>
        <!-- admin -->
        <div class='form-group form-check'>
            <input type='checkbox' class='form-check-input' $status name='status' id='status'>
            <label class='form-check-label' for='status' >Admin</label>
        </div>
        <!-- categories -->
        <div class='form-group form-check'>";
    $mysqli = dbconnector(1);
    $result = $mysqli->query("SELECT * from category;");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $category = $row['name'];
            $categoryID = $row['tag_ID'];
            $output .= "<input type='checkbox' class='form-check-input' name='check$categoryID' id='check$categoryID'>
            <label class='ml-1 form-check-label' for='check$categoryID'>$category</label>";
        }
    }
    $output .= '</div>
            <button type="submit" name="button" value="submit" class="btn btn-info" id="submitUser">Submit</button>
          </form>
        </div>';
    $mysqli->close();
    return $output;
}
if(isset($_POST['username'])){
    $categories = array();
    $mysqli = dbconnector(1);
    $result = $mysqli->query("SELECT tag_ID from category;");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ID = $row['tag_ID'];
            if(isset($_POST["check$ID"])){
                $categories[$ID] = $ID;
            }
        }
    }
    if(isset($_POST["status"])){
        $status = 1;
    }else{
        $status = 0;
    }
    createUser($_POST['username'], $_POST['firstname'], $_POST['lastname'], $_POST['password'], $status, $categories);
    $mysqli->close();
}
function createUser($username, $firstName, $lastName, $password, $status, $categories){
    $mysqli = dbconnector(1);
    $username = trim(htmlspecialchars($username));
    $firstName = trim(htmlspecialchars($firstName));
    $lastName = trim(htmlspecialchars($lastName));
    $password = password_hash(htmlspecialchars($password), PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare("INSERT INTO users (username, firstName, lastName, hash, Status) values (?,?,?,?,?);");
    $stmt->bind_param("ssssi", $username, $firstName, $lastName, $password, $status);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
    asignCategories($username, $categories);
}
function asignCategories($username, $categories){
    $mysqli = dbconnector(1);
    $result = $mysqli->query("SELECT ID from users where username = $username;");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $userID = $row['ID'];
            foreach ($categories as $category){
                $stmt = $mysqli->prepare("INSERT INTO users_has_kategorie (users_ID, kategorie_Tag_ID) values (?,?);");
                $stmt->bind_param("ii", $userID, $category);
                $stmt->execute();
            }
        }
    }
}