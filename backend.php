<?php
if(!isset($_SESSION)){
    session_start();
}

function dbConnector($admin){
    $host     = 'localhost';       // host
    $password = '123456';        // Passwort
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

if(isset($_GET['page']) && $_GET['page'] == "default"){
    if (isset($_SESSION['admin']) == 0){
        $_SESSION['page'] = "todos";
    }elseif (isset($_SESSION['admin']) == 1){
        if(isset($_SESSION['page']) && preg_match("/user/i", $_SESSION['page'])){
            $_SESSION['page'] = "users";
        }elseif(isset($_SESSION['page']) && preg_match("/categor/i", $_SESSION['page'])){
            $_SESSION['page'] = "categories";
        }else{
            $_SESSION['page'] = "todos";
        }
    }else{
        $_SESSION['page'] = "login";
    }
    echo "<meta http-equiv='refresh' content='0;url=index.php'>";
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
if(isset($_POST['usernameLogin']) && isset($_POST['passwordLogin'])){
    $mysqli = dbConnector(1);

    $username = htmlspecialchars(trim($_POST['usernameLogin']));
    $password = htmlspecialchars($_POST['passwordLogin']);
    $result = $mysqli->query("SELECT * from m151.users where username = '$username'");
    if ($result->num_rows == 1) {
        while($row = $result->fetch_assoc()) {
            if(password_verify($password, $row['hash']) && !empty($row['username'])){
                $_SESSION['ID'] = $row['ID'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['firstName'] = $row['firstName'];
                $_SESSION['lastName'] = $row['lastName'];
                $_SESSION['admin'] = $row['status'];
                $_SESSION['page'] = "todos";
                session_regenerate_id();
            }else{
                echo "Benutzername oder Passwort sind falsch";
            }
            $mysqli->close();
            echo "<meta http-equiv='refresh' content='0;url=index.php'>";
        }
    }
}


if(isset($_GET['logout'])){
    logout();
}
function logout(){
    session_destroy();
    echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}


//TODOS
if(isset($_GET['page']) && $_GET['page'] == "todos"){
    $_SESSION['page'] = "todos";
    echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}
function todoPage(){
    $output = '<table class="table">
  <thead>
    <tr>
      <th scope="col">ID</th>
      <th scope="col">Title</th>
      <th scope="col">Priority</th>
      <th scope="col">Created on: </th>
      <th scope="col">Due Date:</th>
      <th scope="col">Progress</th>
      <th scope="col">Created by:</th>
      <th scope="col">Category:</th>
    </tr>
  </thead>
  <tbody>';
    $mysqli = dbConnector(1);
    $result = $mysqli->query("SELECT t.todo_ID, t.title, t.createDate, t.dueDate, t.progress, t.priority, u.username, c.name from m151.todo as t join m151.users as u on u.ID = t.users_ID join m151.category as c on c.tag_ID = t.category_tag_ID;");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $id = $row['todo_ID'];
            $title = $row['title'];
            $createDate = ltrim($row['createDate'], " ") ;
            $date1 = new DateTime($row['dueDate']);
            $date2 = new DateTime(date("Y-m-d H:i:s"));
            $timeLeft = calculateTime($date1, $date2);
            $progress = $row['progress'];
            $priority = $row['priority'];
            $creator = $row['username'];
            $category = $row['name'];
            $output .= "<tr>
                          <th scope='row'>#$id</th>
                          <td>$title</td>
                          <td>$priority</td>
                          <td>$createDate</td>
                          $timeLeft
                          <td><div class='progress'>
                                <div class='progress-bar' role='progressbar' aria-valuenow='0' aria-valuemin='0' aria-valuemax='100' style='min-width: 2em; width: $progress%;'>
                                    $progress%
                                </div>
                            </div></td>
                          <td>$creator</td>
                          <td>$category</td>
                          <td><a class='btn btn-success' href='backend.php?viewTodo=$id' role='button'>View Content</a></td>
                          <td><a class='btn btn-info' href='backend.php?editTodo=$id' role='button'>Edit</a></td>
                          <td><a class='btn btn-danger' href='backend.php?deleteTodo=$id' role='button'>Delete</a></td>
                        </tr>";
        }
    }else{
        $output .= "<td>no results</td>";
    }
    $output .= "</tbody></table>";
    $mysqli->close();
    return $output;
}
function calculateTime($date1, $date2){
    $interval = $date1->diff($date2);
    if($date1 > $date2){
        if($interval->y > 0){
            return "<td class='bg-success'>Due in: $interval->y Years, $interval->m Months</td>";
        }elseif ($interval->m >0){
            return "<td class='bg-success'>Due in: $interval->m Months, $interval->d Days</td>";
        }elseif ($interval->d > 0){
            return "<td class='bg-success'>Due in: $interval->d Days, $interval->h Hours</td>";
        }elseif ($interval->h > 0){
            return "<td class='bg-success'>Due in: $interval->h Hours, $interval->i Minutes</td>";
        }elseif ($interval->i > 0){
            return "<td class='bg-success'>Due in: $interval->i Minutes, $interval->s Seconds</td>";
        }elseif ($interval->s > 0){
            return "<td class='bg-success'>Due in: $interval->s Seconds!</td>";
        }else{
            return "<td class='bg-danger'>Due now!</td>";
        }
    } else{
        if($interval->y > 0){
            return "<td class='bg-danger'>Past Due: $interval->y Years, $interval->m Months</td>";
        }elseif ($interval->m >0){
            return "<td class='bg-danger'>Past Due: $interval->m Months, $interval->d Days</td>";
        }elseif ($interval->d > 0){
            return "<td class='bg-danger'>Past Due: $interval->d Days, $interval->h Hours</td>";
        }elseif ($interval->h > 0){
            return "<td class='bg-danger'>Past Due: $interval->h Hours, $interval->i Minutes</td>";
        }elseif ($interval->i > 0){
            return "<td class='bg-danger'>Past Due: $interval->i Minutes, $interval->s Seconds</td>";
        }elseif ($interval->s > 0){
            return "<td class='bg-danger'>Past Due: $interval->s Seconds!</td>";
        }
    }

// shows the total amount of days (not divided into years, months and days like above)
    //return "difference " . $interval->days . " days ";
}

if(isset($_GET['page']) && $_GET['page'] == "newTodo"){
    $_SESSION['page'] = "newTodo";
    echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}
function todoForm($title = "", $content = "", $priority = "", $dueDate = "", $progress = "", $categoryPreset = ""){
    $output = "<div class='container'>
    <h1>Registrierung</h1>

    <form action='backend.php' method='post'>
        <!-- benutzername -->
        <div class='form-group'>
            <label for='username'>Title</label>
            <input type='text' name='title' class='form-control' id='title'
            value='$title'
                   placeholder='Title'
                   maxlength='30' required>
        </div>
        <div class='form-group'>
            <label for='username'>Content</label>
            <textarea class='form-control' rows='5' id='content' maxlength='255' required>$content</textarea>
        </div>
        
        <div class='form-group'>
            <label for='lastname'>Priority 1(high) - 5(low)</label>
            <input type='text' name='priority' class='form-control' id='priority'
            value='$priority'
                   placeholder='priority'
                   maxlength='1'
                   required>
        </div>
        <!-- vorname -->
        <div class='form-group'>
            <label for='firstname'>Due Date</label>
            <input type='datetime-local' name='dueDate' class='form-control' id='firstname'
            value='$dueDate'
                   placeholder='First Name'
                   required>
        </div>
        <!-- nachname -->
        <div class='form-group'>
            <label for='lastname'>Progress (0 - 100)</label>
            <input type='text' name='progress' class='form-control' id='progress'
            value='$progress'
                   placeholder='progress'
                   maxlength='3'
                   required>
        </div>
        <!-- categories -->
        <label>Category: </label>
        <div class='form-check'>";
    $mysqli = dbConnector(0);
    $userID = $_SESSION['ID'];
    $result = $mysqli->query("SELECT c.name, c.tag_ID from m151.category as c join m151.users_has_category as uhc on c.tag_ID = uhc.category_tag_ID where uhc.users_ID = '$userID';");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $checked = "";
            $category = $row['name'];
            $categoryID = $row['tag_ID'];
            if($categoryPreset == $categoryID){
                $checked = "checked";
            }
            $output .= "<label class='form-check-label' for='flexRadioDefault1' > 
                            <input class='form-check-input' type='radio' name='flexRadioDefault' id='flexRadioDefault1' value='$categoryID' $checked>
                            $category
                        </label>
                        ";
        }

    }
    $output .= '</div>
            <button type="submit" name="button" value="submit" class="btn btn-info" id="submitUser">Submit</button>
          </form>
        </div>';
    $mysqli->close();
    return $output;
}

if (isset($_POST['title'])){
    createTodo($_POST['title'], $_POST['content'], $_POST['dueDate'], $_POST['progress'], $_POST['priority'], $_POST['category']);
}
function createTodo($title, $content, $dueDate, $progress, $priority, $categoryID){
    $title = trim(htmlspecialchars($title));
    $content = trim(htmlspecialchars($content));
    $createDate = date("Y-m-d H:i:s");
    $dueDate = trim(htmlspecialchars($dueDate));
    $progress = trim(htmlspecialchars($progress));
    $priority = trim(htmlspecialchars($priority));
    $usersID = $_SESSION['ID'];

    $mysqli = dbConnector(1);
    $stmt = $mysqli->prepare("INSERT INTO m151.todo (title, content, createDate, dueDate, progress, priority, users_ID, category_tag_ID) VALUES (?,?,?,?,?,?,?,?);");
    $stmt->bind_param("ssssiiii", $title, $content, $createDate, $dueDate, $progress, $priority, $usersID, $categoryID);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
    $_SESSION['page'] = "todos";
    //echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}

function editTodo($todoID){

}

function deleteTodo(){

}


//USERS
if(isset($_GET['page']) && $_GET['page'] == "users"){
    $_SESSION['page'] = "users";
    echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}
function usersPage(){
    $output = '<table class="table">
  <thead>
    <tr>
      <th scope="col">ID</th>
      <th scope="col">Username</th>
      <th scope="col">First Name</th>
      <th scope="col">Last Name</th>
      <th scope="col">Categories</th>
      <th scope="col">Admin</th>
    </tr>
  </thead>
  <tbody>';
    $mysqli = dbConnector(1);
    $result = $mysqli->query("SELECT * from m151.users;");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $id = $row['ID'];
            $username = $row['username'];
            $firstName = $row['firstName'];
            $lastName = $row['lastName'];
            $status = $row['status'];
            $output .= "<tr>
                          <th scope='row'>#$id</th>
                          <td>$username</td>
                          <td>$firstName</td>
                          <td>$lastName</td>
                          <td>";
            $result2 = $mysqli->query("select c.name from m151.users as u join m151.users_has_category as uk on u.ID = uk.users_ID join m151.category as c on c.tag_ID = uk.category_tag_ID where u.username = '$username';");

                if ($result2->num_rows> 0) {
                    while ($row2 = $result2->fetch_assoc()) {
                        $category = $row2['name'];
                        $output .= "$category, ";
                    }
                    $output = rtrim($output, ", ");
                }else{
                    $output .=  "";
                }
            $output .= "</td>
                          <td>$status</td>
                          <td><a class='btn btn-info' href='backend.php?editUser=$id' role='button'>Edit</a></td>
                          <td><a class='btn btn-danger' href='backend.php?deleteUser=$id' role='button'>Delete</a></td>
                        </tr>";
        }
    }else{
        $output .= "<td>no results</td>";
    }
    $output .= "</tbody></table>";
    $mysqli->close();
    return $output;
}

if(isset($_GET['page']) && $_GET['page'] == "newUser"){
    $_SESSION['page'] = "newUser";
    echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}
function userForm($username = "", $firstName = "", $lastName = "", $categories = [], $status = ""){
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
        <label>Categories: </label>
        <div class='form-group form-check'>";
    $mysqli = dbConnector(1);
    $result = $mysqli->query("SELECT * from m151.category;");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $checked = "";
            $category = $row['name'];
            $categoryID = $row['tag_ID'];
            foreach ($categories as $instance){
                if($instance == $categoryID){
                    $checked = "checked";
                }
            }
            $output .= "<label class='form-check-label' for='check$categoryID'><input type='checkbox' class='form-check-input' $checked name='check$categoryID' id='check$categoryID' value='$categoryID'>
             $category </label> ";
        }

    }
    $output .= '</div>
            <button type="submit" name="button" value="submit" class="btn btn-info" id="submitUser">Submit</button>
          </form>
        </div>';
    $mysqli->close();
    return $output;
}
//create User
if(isset($_POST['username'])){
    $categories = array();
    $mysqli = dbConnector(1);
    $result = $mysqli->query("SELECT tag_ID from m151.category;");
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
    $mysqli = dbConnector(1);
    $username = trim(htmlspecialchars($username));
    $firstName = trim(htmlspecialchars($firstName));
    $lastName = trim(htmlspecialchars($lastName));
    $password = password_hash(htmlspecialchars($password), PASSWORD_DEFAULT);
    if(!empty($_SESSION['editUser'])){
        $userID = $_SESSION['editUser'];
        $stmt = $mysqli->prepare("UPDATE m151.users SET username = ?, firstName = ?, lastName = ?, hash = ?, status = ? WHERE ID = '$userID'");
        $_SESSION['editUser'] = "";

    }else{
        $stmt = $mysqli->prepare("INSERT INTO m151.users (username, firstName, lastName, hash, status) values (?,?,?,?,?);");
    }
    $stmt->bind_param("ssssi", $username, $firstName, $lastName, $password, $status);
    $stmt->execute();
    $result = $mysqli->query("SELECT ID from m151.users where username = '$username';");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $userID = $row['ID'];
            foreach ($categories as $category){
                $stmt = $mysqli->prepare("INSERT INTO m151.users_has_category (users_ID, category_tag_ID) values (?,?);");
                $stmt->bind_param("ii", $userID, $category);
                $stmt->execute();
            }
        }
    }
    $stmt->close();
    $mysqli->close();
    $_SESSION['page'] = "users";
    echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}
//edit User
if(isset($_GET['editUser'])){
    $_SESSION['page'] = "editUser";
    $_SESSION['editUser'] = $_GET['editUser'];
    echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}
function editUser($userID)
{
    $mysqli = dbConnector(1);
    $result = $mysqli->query("SELECT * from m151.users where ID = '$userID';");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $username = $row['username'];
            $firstName = $row['firstName'];
            $lastName = $row['lastName'];
            if ($row['status'] == 1) {
                $status = "checked";
            } else {
                $status = "";
            }
            $result2 = $mysqli->query("select c.tag_ID from m151.users as u join m151.users_has_category as uk on u.ID = uk.users_ID join m151.category as c on c.tag_ID = uk.category_tag_ID where u.username = '$username';");

            $categories = [];

            if ($result2->num_rows > 0) {
                $i = 0;
                while ($row2 = $result2->fetch_assoc()) {
                    $categories[$i] = $row2['tag_ID'];
                    $i++;
                }
            }
            return userForm($username, $firstName, $lastName, $categories, $status, $userID);
        }
    }
}
//delete User
if(isset($_GET['deleteUser'])){
    deleteUser($_GET['deleteUser']);
}
function deleteUser($id){
    $mysqli = dbConnector(1);
    $stmt = $mysqli->prepare("delete from m151.users where ID = '$id';");
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
    echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}


//CATEGORIES
function categoriesPage(){

}

function categoryForm(){

}

function createCategory(){

}

function editCategory(){

}

function deleteCategory(){

}