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
        }
    }else{
        $_SESSION['page'] = "login";
    }
    echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}

//LOGIN / LOGOUT
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
                if($_SESSION['admin'] == 1){
                    $_SESSION['page'] = "users";
                }else{
                    $_SESSION['page'] = "todos";
                }
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
if(isset($_GET['viewTodo'])){
    $_SESSION['page'] = "viewTodo";
    $_SESSION['viewTodo'] = $_GET['viewTodo'];
    echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}
function todoPage($viewTodo = ""){
    $_SESSION['editTodo'] = "";
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
    $userID = $_SESSION['ID'];
    $result = $mysqli->query("SELECT t.todo_ID, t.title, t.content, t.createDate, t.dueDate, t.progress, t.priority, u.username, t.users_ID, c.name, t.archived from m151.todo as t join m151.users as u on u.ID = t.users_ID join m151.category as c on c.tag_ID = t.category_tag_ID join m151.users_has_category uhc on c.tag_ID = uhc.category_tag_ID where uhc.users_ID = '$userID';");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if($row['archived'] == 0) {
                $id = $row['todo_ID'];
                $title = $row['title'];
                $content = $row['content'];
                $createDate = ltrim($row['createDate'], " ");
                $date1 = new DateTime($row['dueDate']);
                $date2 = new DateTime(date("Y-m-d H:i:s"));
                $timeLeft = calculateTime($date1, $date2);
                $progress = $row['progress'];
                $priority = $row['priority'];
                $creator = $row['username'];
                $creatorID = $row['users_ID'];
                $category = $row['name'];

                $output .= "<tr>
                          <th scope='row'>#$id</th>
                          <td style='word-wrap: break-word;'>$title</td>
                          <td>$priority</td>
                          <td>$createDate</td>
                          $timeLeft
                          <td><div class='progress'>
                                <div class='progress-bar' role='progressbar' aria-valuenow='0' aria-valuemin='0' aria-valuemax='100' style='min-width: 2em; width: $progress%;'>
                                    $progress%
                                </div>
                            </div></td>
                          <td>$creator</td>
                          <td>$category</td>";
                if ($userID == $creatorID || $_SESSION['admin'] == 1) {
                    $output .= "<td><a class='btn btn-info' href='backend.php?editTodo=$id' role='button'>Edit</a></td>
                          <td><a class='btn btn-danger' href='backend.php?deleteTodo=$id' role='button'>Delete</a></td>";
                } else {
                    $output .= "<td></td><td></td>";
                }
                if (isset($viewTodo) && $viewTodo == $id) {
                    $output .= "<td><a class='btn btn-success' href='backend.php?page=default' role='button'>View Content <span class='glyphicon glyphicon-chevron-up' aria-hidden='true'></span></a></td>
                            </tr>
                            <tr><td colspan='12' style='word-wrap: break-word;'>$content</td></tr>";
                } else {
                    $output .= "<td><a class='btn btn-success' href='backend.php?viewTodo=$id' role='button'>View Content <span class='glyphicon glyphicon-chevron-down' aria-hidden='true'></span></a></td>
                            <td><a class='btn btn-success' href='backend.php?archiveTodo=$id' role='button'>Archive</a></td>
                        </tr>";
                }
            }
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
    return "";
}

if(isset($_GET['page']) && $_GET['page'] == "newTodo"){
    $_SESSION['page'] = "newTodo";
    echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}
function todoForm($title = "", $content = "", $priority = "", $dueDate = "", $progress = "", $categoryPreset = ""){
    $dateTime = substr($dueDate, 0, 10)."T".substr($dueDate, 11, 5);
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
            <textarea class='form-control' rows='5' name='content' id='content' maxlength='255'>$content</textarea>
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
            value='$dateTime'
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
    $mysqli = dbConnector(1);
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
                            <input class='form-check-input' type='radio' name='category' id='flexRadioDefault1' value='$categoryID' $checked required>
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
    if(!empty($_SESSION['editTodo'])){
        $stmt = $mysqli->prepare("UPDATE m151.todo SET title=?, content=?, dueDate=?, progress=?, priority=?, category_tag_ID=? where users_ID = '$usersID'");
        $stmt->bind_param("sssiii", $title, $content, $dueDate, $progress, $priority, $categoryID);
        $_SESSION['editTodo'] = "";
    }else{
        $stmt = $mysqli->prepare("INSERT INTO m151.todo (title, content, createDate, dueDate, progress, priority, users_ID, category_tag_ID) VALUES (?,?,?,?,?,?,?,?);");
        $stmt->bind_param("ssssiiii", $title, $content, $createDate, $dueDate, $progress, $priority, $usersID, $categoryID);
    }
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
    $_SESSION['page'] = "todos";
    echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}

if(isset($_GET['editTodo'])){
    $_SESSION['page'] = "editTodo";
    $_SESSION['editTodo'] = $_GET['editTodo'];
    echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}
function editTodo($todoID){
    $mysqli = dbConnector(1);
    $result = $mysqli->query("SELECT * from m151.todo where todo_ID = '$todoID';");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
            $title = $row['title'];
            $content = $row['content'];
            $dueDate = $row['dueDate'];
            $progress = $row['progress'];
            $priority = $row['priority'];
            $categoryID = $row['category_tag_ID'];

            return todoForm($title, $content, $priority, $dueDate, $progress, $categoryID);
    }else{
        return "todo not found";
    }
}

if(isset($_GET['archiveTodo'])){
    archiveTodo($_GET['archiveTodo']);
}
function archiveTodo($todoID){
    $mysqli = dbConnector(1);
    $stmt = $mysqli->prepare("UPDATE m151.todo SET archived = '1' WHERE todo_ID = '$todoID';");
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
    echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}

if(isset($_GET['deleteTodo'])){
    deleteTodo($_GET['deleteTodo']);
}
function deleteTodo($todoID){
    $mysqli = dbConnector(1);
    $stmt = $mysqli->prepare("delete from m151.todo where todo_ID = '$todoID';");
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
    echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}


//USERS
if(isset($_GET['page']) && $_GET['page'] == "users"){
    $_SESSION['page'] = "users";
    echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}
function usersPage(){
    $_SESSION['editUser'] = "";
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
function userForm($username = "", $firstName = "", $lastName = "", $categories = [], $status = "", $userError = ""){

    $output = "<div class='container'>
    <h1>User Form</h1>";
    if(!empty($userError)){
        $output .= "<div class=\"alert alert-danger\" role=\"alert\">" . $userError . "</div>";
    }
    $output .= "<form action='backend.php' method='post'>
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

if(isset($_POST['username'])) {
    $userError[0] = "";
    if (isset($_POST['username']) && !empty(trim($_POST['username'])) && strlen(trim($_POST['username'])) <= 30) {
        $username = trim($_POST['username']);
        // entspricht der benutzername unseren vogaben (minimal 6 Zeichen, Gross- und Kleinbuchstaben)
        if (!preg_match("/(?=.*[a-z])(?=.*[A-Z])[a-zA-Z]{6,}/", $username)) {
            $userError[0] .= "Der Benutzername entspricht nicht dem geforderten Format.<br />";
        }
    } else {
        // Ausgabe Fehlermeldung
        $userError[0] .= "Geben Sie bitte einen korrekten Benutzernamen ein.<br />";
    }
    $userError['username'] = trim(htmlspecialchars($_POST['username']));
    if (!isset($_POST['firstname']) || empty(trim($_POST['firstname'])) || strlen(trim($_POST['firstname'])) > 45) {
        // Spezielle Zeichen Escapen > Script Injection verhindern
        $userError[0] .= "Geben Sie bitte einen korrekten Vornamen ein.<br />";
    }
    $userError['firstname'] = htmlspecialchars(trim($_POST['firstname']));
// nachname vorhanden, mindestens 1 Zeichen und maximal 30 zeichen lang
    if (!isset($_POST['lastname']) || empty(trim($_POST['lastname'])) || strlen(trim($_POST['lastname'])) > 45) {
        // Spezielle Zeichen Escapen > Script Injection verhindern
        $userError[0] .= "Geben Sie bitte einen korrekten Nachnamen ein.<br />";
    }
    $userError['lastname'] = htmlspecialchars(trim($_POST['lastname']));
// passwort vorhanden, mindestens 8 Zeichen
    if (isset($_POST['password']) && !empty(trim($_POST['password']))) {
        $password = trim($_POST['password']);
        //entspricht das passwort unseren vorgaben? (minimal 8 Zeichen, Zahlen, Buchstaben, keine Zeilenumbrüche, mindestens ein Gross- und ein Kleinbuchstabe)
        if (!preg_match("/(?=^.{8,}$)((?=.*\d+)(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/", $password)) {
            $userError[0] .= "Das Passwort entspricht nicht dem geforderten Format.<br />";
            $password = trim(htmlspecialchars($_POST['password']));
        }
    } else {
        // Ausgabe Fehlermeldung
        $userError[0] .= "Geben Sie bitte ein korrektes Passwort ein.<br />";
    }
// wenn kein Fehler vorhanden ist, schreiben der Daten in die Datenbank
    $categories = array();
    $mysqli = dbConnector(1);
    $result = $mysqli->query("SELECT tag_ID from m151.category;");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ID = $row['tag_ID'];
            if (isset($_POST["check$ID"])){
                $categories[$ID] = $ID;
            }
        }
    }
    $mysqli->close();
    $userError['categories'] = $categories;
    if (isset($_POST["status"])) {
        $userError['status'] = 1;
    } else {
        $userError['status'] = 0;
    }
    if (empty($userError[0])) {
        createUser($userError['username'], $userError['firstname'], $userError['lastname'], $password, $userError['status'], $userError['categories']);
    } else {
        $_SESSION['userError'] = $userError;
        echo "<meta http-equiv='refresh' content='0;url=index.php'>";
    }
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
            $result1 = $mysqli->query("SELECT * from m151.category;");
            $result2 = $mysqli->query("SELECT * from m151.users_has_category where users_ID = '$userID';");
            for ($i=0;$row1 = $result1->fetch_assoc();$i++) {
                $ID1[$i] = $row1['tag_ID'];
            }
            if ($result2->num_rows > 0) {
                for ($j=0;$row2 = $result2->fetch_assoc();$j++) {
                    $ID2[$j] = $row2['category_tag_ID'];
                }
            }
            $j=0;
            for($y=0;$y < $i;$y++){
                if(isset($ID2[$j])){
                    if (isset($categories[$ID1[$y]]) && $ID1[$y] == $ID2[$j]) {
                        $j++;
                    } elseif(!isset($categories[$ID1[$y]]) && $ID1[$y] == $ID2[$j]) {
                        $stmt = $mysqli->prepare("DELETE FROM m151.users_has_category WHERE users_ID = '$userID' and category_tag_ID = '$ID2[$j]'");
                        $stmt->execute();
                        $j++;
                    }elseif(isset($categories[$ID1[$y]])){
                        $stmt = $mysqli->prepare("INSERT INTO m151.users_has_category (users_ID, category_tag_ID) values (?,?);");
                        $stmt->bind_param("ii", $userID, $ID1[$y]);
                        $stmt->execute();
                    }
                }elseif(isset($categories[$ID1[$y]])){
                    $stmt = $mysqli->prepare("INSERT INTO m151.users_has_category (users_ID, category_tag_ID) values (?,?);");
                    $stmt->bind_param("ii", $userID, $ID1[$y]);
                    $stmt->execute();
                }
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
function editUser($userID){
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
                while ($row2 = $result2->fetch_assoc()) {
                    $categories[$row2['tag_ID']] = $row2['tag_ID'];
                }
            }
            return userForm($username, $firstName, $lastName, $categories, $status);
        }
    }
    return "No User selected";
}
//delete User
if(isset($_GET['deleteUser'])){
    deleteUser($_GET['deleteUser']);
}
function deleteUser($userID){
    $mysqli = dbConnector(1);
    $stmt = $mysqli->prepare("delete from m151.users where ID = '$userID';");
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
    echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}


//CATEGORIES
if(isset($_GET['page']) && $_GET['page'] == "categories"){
    $_SESSION['page'] = "categories";
    echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}
function categoriesPage(){
    $_SESSION['editCategory'] = "";
    $output = '<table class="table">
  <thead>
    <tr>
      <th scope="col">ID</th>
      <th scope="col">Name</th>
      <th scope="col">Users</th>
    </tr>
  </thead>
  <tbody>';
    $mysqli = dbConnector(1);
    $userID = $_SESSION['ID'];
    $result = $mysqli->query("SELECT * from m151.category;");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $id = $row['tag_ID'];
            $name = $row['name'];

            $output .= "<tr>
                          <th scope='row'>#$id</th>
                          <td style='word-wrap: break-word;'>$name</td>
                          <td>
                          ";
            $result2 = $mysqli->query("select u.username from m151.users as u join m151.users_has_category as uk on u.ID = uk.users_ID where uk.category_tag_ID = '$id';");

            if ($result2->num_rows> 0) {
                while ($row2 = $result2->fetch_assoc()) {
                    $username = $row2['username'];
                    $output .= "$username, ";
                }
                $output = rtrim($output, ", ");
            }else{
                $output .=  "";
            }
            $output .= "</td>
                            <td><a class='btn btn-info' href='backend.php?editCategory=$id' role='button'>Edit</a></td>
                            <td><a class='btn btn-danger' href='backend.php?deleteCategory=$id' role='button'>Delete</a></td>
                            </tr>";
        }
    }else{
        $output .= "<td>no results</td>";
    }
    $output .= "</tbody></table>";
    $mysqli->close();
    return $output;}

if(isset($_GET['page']) && $_GET['page'] == "newCategory"){
    $_SESSION['page'] = "newCategory";
    echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}
function categoryForm($name = ""){

    $output = "<div class='container'>
    <h1>Category Form</h1>

    <form action='backend.php' method='post'>
        <!-- benutzername -->
        <div class='form-group'>
            <label for='username'>Name</label>
            <input type='text' name='catName' class='form-control' id='catName'
            value='$name'
                   placeholder='Name'
                   maxlength='30' required>
        </div>
            <button type='submit' name='button' value='submit' class='btn btn-info' id='submitUser'>Submit</button>          </form>
        </div>";
    return $output;
}

if(isset($_POST['catName'])){
    createCategory($_POST['catName']);
}
function createCategory($name){
    $name = trim(htmlspecialchars($name));
    $mysqli = dbConnector(1);
    if(!empty($_SESSION['editCategory'])){
        $catID = $_SESSION['editCategory'];
        $stmt = $mysqli->prepare("UPDATE m151.category SET name = ? WHERE tag_ID = '$catID'");
        $_SESSION['editCategory'] = "";
    }else{
        $stmt = $mysqli->prepare("INSERT INTO m151.category (name) VALUES (?)");
    }
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
    $_SESSION['page'] = "categories";
    echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}

if(isset($_GET['editCategory'])){
    $_SESSION['page'] = "editCategory";
    $_SESSION['editCategory'] = $_GET['editCategory'];
    echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}
function editCategory($catID){
    $mysqli = dbConnector(1);
    $result = $mysqli->query("SELECT name FROM m151.category WHERE tag_ID = '$catID'");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $name = $row['name'];
        }
    }
    return categoryForm($name);
}

if(isset($_GET['deleteCategory'])){
    deleteCategory($_GET['deleteCategory']);
}
function deleteCategory($tagID){
    $mysqli = dbConnector(1);
    $stmt = $mysqli->query("DELETE FROM m151.category WHERE tag_ID = '$tagID'");
    $mysqli->close();
    echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}