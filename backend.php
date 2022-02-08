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
    if (isset($_SESSION['admin']) && $_SESSION['admin'] == 0){
        $_SESSION['page'] = "todos";
    }elseif (isset($_SESSION['admin']) && $_SESSION['admin'] == 1){
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
function login($error = "", $username = ""){
    $output = "<div class='container'>
    <div class='row vertical-offset-100'>
        <div class='col-md-4 col-md-offset-4'>
            <div class='panel panel-default'>";
    if(!empty($error)){
        $output .= "<div class=\"alert alert-danger\" role=\"alert\">" . $error . "</div>";
    }
    $output .= "<div class='panel-heading'>
                    <h3 class='panel-title'>Please sign in</h3>
                </div>
                <div class='panel-body'>
                    <form accept-charset='UTF-8' role='form' method='post' action='backend.php'>
                        <fieldset>
                            <div class='form-group'>
                                <input class='form-control' placeholder='Username' name='usernameLogin' type='text' value='$username' required>
                            </div>
                            <div class='form-group'>
                                <input class='form-control' placeholder='Password' name='passwordLogin' type='password' required>
                            </div>
                            <input class='btn btn-lg btn-success btn-block' type='submit' value='Login'>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>";
    return $output;
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
                $loginError['error'] = "Passwort falsch.</br>";
                $loginError['username'] = $_POST['usernameLogin'];
                $_SESSION['loginError'] = $loginError;
            }
            $mysqli->close();

        }
    }else{
        $loginError['error'] = "Benutzername falsch.</br>";
        $loginError['username'] = $_POST['usernameLogin'];
        $_SESSION['loginError'] = $loginError;
    }
    echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}

if(isset($_GET['logout'])){
    logout();
}
function logout(){
    session_destroy();
    echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}

//I don't even know
if(isset($_GET['sortRow'])){
    if(isset($_GET['sortDir']) && $_GET['sortDir'] == "desc"){
        $_SESSION['sortDir'] = $_GET['sortDir'];
    }else{
        $_SESSION['sortDir'] = "asc";
    }
    if($_SESSION['page'] == "todos" || $_SESSION['page'] == "viewTodo"){
        if($_GET['sortRow'] == "id"){
            $_SESSION['sortRow'] = "t.todo_ID";
        }
        if($_GET['sortRow'] == "title"){
            $_SESSION['sortRow'] = "t.title";
        }
        if($_GET['sortRow'] == "priority"){
            $_SESSION['sortRow'] = "t.priority";
        }
        if($_GET['sortRow'] == "createDate"){
            $_SESSION['sortRow'] = "t.createDate";
        }
        if($_GET['sortRow'] == "dueDate"){
            $_SESSION['sortRow'] = "t.dueDate";
        }
        if($_GET['sortRow'] == "progress"){
            $_SESSION['sortRow'] = "t.progress";
        }

        if($_GET['sortRow'] == "creator"){
            $_SESSION['sortRow'] = "u.username";
        }
        if($_GET['sortRow'] == "category"){
            $_SESSION['sortRow'] = "c.name";
        }
    }
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
    $output = '<table class="table" id="myTable">
                  <thead>
                    <tr>';
    /*if(!preg_match("/(t.todo_ID)(t.title)(t.priority)(t.createDate)(t.dueDate)(t.progress)(u.username)(c.name)/",$_SESSION['sortRow']) && $_SESSION['page'] == "todos"){
        $_SESSION['sortRow'] = "t.todo_ID";
        $_SESSION['sortDir'] = "asc";
    }*/
    if(isset($_SESSION['sortRow'])){
        if($_SESSION['sortRow'] == "t.todo_ID"){
            if(isset($_SESSION['sortDir']) && $_SESSION['sortDir'] == "asc"){
                $output .= '<th scope="col"><a id="btn" class="btn" href="backend.php?sortRow=id&sortDir=desc">ID <span class="glyphicon glyphicon-sort-by-attributes" aria-hidden="true"></span></a></th>';
            }else{
                $output .= '<th scope="col"><a id="btn" class="btn" href="backend.php?sortRow=id&sortDir=asc">ID <span class="glyphicon glyphicon-sort-by-attributes-alt" aria-hidden="true"></span></a></th>';
            }
        }else{
            $output .= '<th scope="col"><a id="btn" class="btn" href="backend.php?sortRow=id&sortDir=asc">ID <span class="glyphicon glyphicon-sort" aria-hidden="true"></span></a></th>';
        }
        if($_SESSION['sortRow'] == "t.title"){
            if(isset($_SESSION['sortDir']) && $_SESSION['sortDir'] == "asc"){
                $output .= '<th scope="col"><a id="btn" class="btn" href="backend.php?sortRow=title&sortDir=desc">Title <span class="glyphicon glyphicon-sort-by-attributes" aria-hidden="true"></span></a></th>';
            }else{
                $output .= '<th scope="col"><a id="btn" class="btn" href="backend.php?sortRow=title&sortDir=asc">Title <span class="glyphicon glyphicon-sort-by-attributes-alt" aria-hidden="true"></span></a></th>';
            }
        }else{
            $output .= '<th scope="col"><a id="btn" class="btn" href="backend.php?sortRow=title&sortDir=asc">Title <span class="glyphicon glyphicon-sort" aria-hidden="true"></span></a></th>';
        }
        if($_SESSION['sortRow'] == "t.priority"){
            if(isset($_SESSION['sortDir']) && $_SESSION['sortDir'] == "asc"){
                $output .= '<th scope="col"><a id="btn" class="btn" href="backend.php?sortRow=priority&sortDir=desc">Priority <span class="glyphicon glyphicon-sort-by-attributes" aria-hidden="true"></span></a></th>';
            }else{
                $output .= '<th scope="col"><a id="btn" class="btn" href="backend.php?sortRow=priority&sortDir=asc">Priority <span class="glyphicon glyphicon-sort-by-attributes-alt" aria-hidden="true"></span></a></th>';
            }
        }else{
            $output .= '<th scope="col"><a id="btn" class="btn" href="backend.php?sortRow=priority&sortDir=asc">Priority <span class="glyphicon glyphicon-sort" aria-hidden="true"></span></a></th>';
        }
        if($_SESSION['sortRow'] == "t.createDate"){
            if(isset($_SESSION['sortDir']) && $_SESSION['sortDir'] == "asc"){
                $output .= '<th scope="col"><a id="btn" class="btn" href="backend.php?sortRow=createDate&sortDir=desc">Created on <span class="glyphicon glyphicon-sort-by-attributes" aria-hidden="true"></span></a></th>';
            }else{
                $output .= '<th scope="col"><a id="btn" class="btn" href="backend.php?sortRow=createDate&sortDir=asc">Created on <span class="glyphicon glyphicon-sort-by-attributes-alt" aria-hidden="true"></span></a></th>';
            }
        }else{
            $output .= '<th scope="col"><a id="btn" class="btn" href="backend.php?sortRow=createDate&sortDir=asc">Created on <span class="glyphicon glyphicon-sort" aria-hidden="true"></span></a></th>';
        }
        if($_SESSION['sortRow'] == "t.dueDate"){
            if(isset($_SESSION['sortDir']) && $_SESSION['sortDir'] == "asc"){
                $output .= '<th scope="col"><a id="btn" class="btn" href="backend.php?sortRow=dueDate&sortDir=desc">Due <span class="glyphicon glyphicon-sort-by-attributes" aria-hidden="true"></span></a></th>';
            }else{
                $output .= '<th scope="col"><a id="btn" class="btn" href="backend.php?sortRow=dueDate&sortDir=asc">Due <span class="glyphicon glyphicon-sort-by-attributes-alt" aria-hidden="true"></span></a></th>';
            }
        }else{
            $output .= '<th scope="col"><a id="btn" class="btn" href="backend.php?sortRow=dueDate&sortDir=asc">Due <span class="glyphicon glyphicon-sort" aria-hidden="true"></span></a></th>';
        }
        if($_SESSION['sortRow'] == "t.progress"){
            if(isset($_SESSION['sortDir']) && $_SESSION['sortDir'] == "asc"){
                $output .= '<th scope="col"><a id="btn" class="btn" href="backend.php?sortRow=progress&sortDir=desc">Progress <span class="glyphicon glyphicon-sort-by-attributes" aria-hidden="true"></span></a></th>';
            }else{
                $output .= '<th scope="col"><a id="btn" class="btn" href="backend.php?sortRow=progress&sortDir=asc">Progress <span class="glyphicon glyphicon-sort-by-attributes-alt" aria-hidden="true"></span></a></th>';
            }
        }else{
            $output .= '<th scope="col"><a id="btn" class="btn" href="backend.php?sortRow=progress&sortDir=asc">Progress <span class="glyphicon glyphicon-sort" aria-hidden="true"></span></a></th>';
        }
        if($_SESSION['sortRow'] == "u.username"){
            if(isset($_SESSION['sortDir']) && $_SESSION['sortDir'] == "asc"){
                $output .= '<th scope="col"><a id="btn" class="btn" href="backend.php?sortRow=creator&sortDir=desc">Creator <span class="glyphicon glyphicon-sort-by-attributes" aria-hidden="true"></span></a></th>';
            }else{
                $output .= '<th scope="col"><a id="btn" class="btn" href="backend.php?sortRow=creator&sortDir=asc">Creator <span class="glyphicon glyphicon-sort-by-attributes-alt" aria-hidden="true"></span></a></th>';
            }
        }else{
            $output .= '<th scope="col"><a id="btn" class="btn" href="backend.php?sortRow=creator&sortDir=asc">Creator <span class="glyphicon glyphicon-sort" aria-hidden="true"></span></a></th>';
        }
        if($_SESSION['sortRow'] == "c.name"){
            if(isset($_SESSION['sortDir']) && $_SESSION['sortDir'] == "asc"){
                $output .= '<th scope="col"><a id="btn" class="btn" href="backend.php?sortRow=category&sortDir=desc">Category <span class="glyphicon glyphicon-sort-by-attributes" aria-hidden="true"></span></a></th>';
            }else{
                $output .= '<th scope="col"><a id="btn" class="btn" href="backend.php?sortRow=category&sortDir=asc">Category <span class="glyphicon glyphicon-sort-by-attributes-alt" aria-hidden="true"></span></a></th>';
            }
        }else{
            $output .= '<th scope="col"><a id="btn" class="btn" href="backend.php?sortRow=category&sortDir=asc">Category <span class="glyphicon glyphicon-sort" aria-hidden="true"></span></a></th>';
        }
    }
    $output .= '</tr>
              </thead>
              <tbody>';
    $mysqli = dbConnector(1);
    $userID = $_SESSION['ID'];
    $order = "order by ".$_SESSION['sortRow']." ".$_SESSION['sortDir'];
    $query = "SELECT t.todo_ID, t.title, t.content, t.createDate, t.dueDate, t.progress, t.priority, u.username, t.users_ID, c.name, t.archived from m151.todo as t join m151.users as u on u.ID = t.users_ID join m151.category as c on c.tag_ID = t.category_tag_ID join m151.users_has_category uhc on c.tag_ID = uhc.category_tag_ID where uhc.users_ID = '$userID' ".$order.";";
    $result = $mysqli->query($query);
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
                if ($userID == $creatorID) {
                    $output .= "<td><a class='btn btn-info' href='backend.php?editTodo=$id' role='button'>Edit</a></td>
                              <td><a class='btn btn-danger' href='backend.php?deleteTodo=$id' role='button'>Delete</a></td>
                              <td><a class='btn btn-success' href='backend.php?archiveTodo=$id' role='button'>Archive</a></td>";
                } else {
                    $output .= "<td colspan='3'></td>";
                }
                if (isset($viewTodo) && $viewTodo == $id) {
                    $output .= "<td><a class='btn btn-success' href='backend.php?page=default' role='button'>View Content <span class='glyphicon glyphicon-chevron-up' aria-hidden='true'></span></a></td>
                            </tr>
                            <tr><td colspan='13' style='word-wrap: break-word;'>$content</td></tr>";
                } else {
                    $output .= "<td><a class='btn btn-success' href='backend.php?viewTodo=$id' role='button'>View Content <span class='glyphicon glyphicon-chevron-down' aria-hidden='true'></span></a></td>
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

if(isset($_GET['page']) && $_GET['page'] == "newTodo"){
    $_SESSION['page'] = "newTodo";
    echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}
function todoForm($title = "", $content = "", $priority = "", $dueDate = "", $progress = "", $categoryPreset = "", $error = ""){
    $dateTime = substr($dueDate, 0, 10)."T".substr($dueDate, 11, 5);
    $output = "<div class='container'>
    <h1>Todo Form</h1>";
    if(!empty($error)){
        $output .= "<div class=\"alert alert-danger\" role=\"alert\">" . $error . "</div>";
    }
    $output .= "<form action='backend.php' method='post'>
        <!-- benutzername -->
        <div class='form-group'>
            <label for='username'>Title</label>
            <input type='text' name='title' class='form-control' id='title'
            value='$title'
                   placeholder='Title'
                   maxlength='45' required>
        </div>
        <div class='form-group'>
            <label for='username'>Content</label>
            <textarea class='form-control' rows='5' name='content' id='content' maxlength='2000'>$content</textarea>
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

if(isset($_POST['title'])){
    $todoError['error'] = "";
    if (!isset($_POST['title']) || empty(trim($_POST['title'])) || strlen(trim($_POST['title'])) > 45) {
        $todoError['error'] .= "Please enter a correct title, no longer than 45 characters.<br />";
    }
    $title = htmlspecialchars(trim($_POST['title']));
    $todoError['title'] = trim(htmlspecialchars($_POST['title']));

    if (strlen(trim($_POST['content'])) > 2000) {
        // Spezielle Zeichen Escapen > Script Injection verhindern
        $todoError['error'] .= "Content: Please limit you content to 2000 characters<br />";
    }
    $todoError['content'] = htmlspecialchars(trim($_POST['content']));

    if (!isset($_POST['priority']) || empty(trim($_POST['priority'])) || strlen(trim($_POST['priority'])) > 1 || !preg_match("/[1-5]+/", trim($_POST['priority']))) {
        $todoError['error'] .= "Priority: Please enter a number between 1 and 5. <br />";
    }
    $todoError['priority'] = htmlspecialchars(trim($_POST['priority']));

    $dueDate = trim($_POST['dueDate']);
    if(!isset($_POST['dueDate']) || empty(trim($_POST['dueDate']))){
        if(!validateDate(trim($_POST['dueDate']))){
            $todoError['error'] .= "Due Date: Please enter a correct DateTime Format<br />";
        }
    }
    $todoError['dueDate'] = htmlspecialchars(trim($_POST['dueDate']));

    if (!isset($_POST['progress']) || empty(trim($_POST['progress'])) || strlen(trim($_POST['progress'])) > 3 || !preg_match("/[0-9]/", $_POST['progress'])) {
        $todoError['error'] .= "Progress: Please enter a number between 1 and 100. <br />";
    }
    $todoError['progress'] = htmlspecialchars(trim($_POST['progress']));

    $todoError['category'] = $_POST['category'];

    if (empty($todoError['error'])) {
        createTodo($todoError['title'], $todoError['content'], $todoError['dueDate'], $todoError['progress'], $todoError['priority'], $todoError['category']);
    } else {
        $_SESSION['todoError'] = $todoError;
        echo "<meta http-equiv='refresh' content='0;url=index.php'>";
    }
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
function validateDate($date, $format = 'Y-m-d H:i:s'){
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
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
    $userError['error'] = "";
    if (isset($_POST['username']) && !empty(trim($_POST['username'])) && strlen(trim($_POST['username'])) <= 30) {
        $username = trim($_POST['username']);
        if (!preg_match("/[a-zA-Z0-9]{6,}/", $username)) {
            $userError['error'] .= "Username must be at least 6 characters, with only letters and numbers.<br />";
        }
    } else {
        $userError['error'] .= "Please enter a username.<br />";
    }
    if(empty($_SESSION['editUser'])){
        $username = trim(htmlspecialchars($_POST['username']));
        $mysqli = dbConnector(1);
        $stmt = $mysqli->query("SELECT * FROM m151.users WHERE username = '$username'");
        if ($stmt->num_rows > 0) {
            $userError['error'] .= "Username already exists.</br>";
        }
    }
    $userError['username'] = trim(htmlspecialchars($_POST['username']));

    if (!isset($_POST['firstname']) || empty(trim($_POST['firstname'])) || strlen(trim($_POST['firstname'])) > 45) {
        $userError['error'] .= "Please enter a first name.<br />";
    }
    $userError['firstname'] = htmlspecialchars(trim($_POST['firstname']));

    if (!isset($_POST['lastname']) || empty(trim($_POST['lastname'])) || strlen(trim($_POST['lastname'])) > 45) {
        $userError['error'] .= "Geben Sie bitte einen korrekten Nachnamen ein.<br />";
    }
    $userError['lastname'] = htmlspecialchars(trim($_POST['lastname']));

    if (isset($_POST['password']) && !empty(trim($_POST['password']))) {
        $password = trim($_POST['password']);
        if (!preg_match("/(?=^.{8,}$)((?=.*\d+)(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/", $password)) {
            $userError['error'] .= "Das Passwort entspricht nicht dem geforderten Format.<br />";
            $password = trim(htmlspecialchars($_POST['password']));
        }
    } else {
        $userError['error'] .= "Geben Sie bitte ein korrektes Passwort ein.<br />";
    }
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
    if (empty($userError['error'])) {
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
function categoryForm($name = "", $error = ""){
    $output = "<div class='container'>
    <h1>Category Form</h1>";
    if(!empty($error)){
        $output .= "<div class=\"alert alert-danger\" role=\"alert\">" . $error . "</div>";
    }

    $output .= "<form action='backend.php' method='post'>
                    <div class='form-group'>
                        <label for='categoryName'>Name  (45)</label>
                        <input type='text' name='categoryName' class='form-control' id='categoryName'
                        value='$name'
                               placeholder='Name'
                               maxlength='30' required>
                    </div>
                    <button type='submit' name='button' value='submit' class='btn btn-info' id='submitUser'>Submit</button>
                </form>
            </div>";
    return $output;
}

if(isset($_POST['categoryName'])){
    $categoryError['error'] = "";
    if (!isset($_POST['categoryName']) || empty(trim($_POST['categoryName'])) || strlen(trim($_POST['categoryName'])) > 45) {
        $categoryError['error'] .= "Please set a name, shorter than 45 Characters.</br>";
    }
    $name = trim(htmlspecialchars($_POST['categoryName']));
    if(empty($_SESSION['editCategory'])){
        $mysqli = dbConnector(1);
        $stmt = $mysqli->query("SELECT * FROM m151.category WHERE name = '$name'");
        if ($stmt->num_rows > 0) {
            $categoryError['error'] .= "Category already exists.</br>";
        }
    }
    $categoryError['name'] = $name;
    if(empty($categoryError['error'])){
        createCategory($name);
    }else{
        $_SESSION['categoryError'] = $categoryError;
        echo "<meta http-equiv='refresh' content='0;url=index.php'>";
    }

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