<?php
function userForm($error = "", $username = "", $firstName = "", $lastName = "", $categories = [], $status = "", $password = ""){

$output = "<div class='container'>
    <h1>User Form</h1>";
    if(!empty($error)){
    $output .= "<div class=\"alert alert-danger\" role=\"alert\">" . $error . "</div>";
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
               maxlength='45' required>
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
if(isset($_POST['firstname']) && !empty(trim($_POST['firstname'])) && strlen(trim($_POST['firstname'])) <= 30){
// Spezielle Zeichen Escapen > Script Injection verhindern
$firstname = htmlspecialchars(trim($_POST['firstname']));
} else {
// Ausgabe Fehlermeldung
$error .= "Geben Sie bitte einen korrekten Vornamen ein.<br />";
}

// nachname vorhanden, mindestens 1 Zeichen und maximal 30 zeichen lang
if(isset($_POST['lastname']) && !empty(trim($_POST['lastname'])) && strlen(trim($_POST['lastname'])) <= 30){
// Spezielle Zeichen Escapen > Script Injection verhindern
$lastname = htmlspecialchars(trim($_POST['lastname']));
} else {
// Ausgabe Fehlermeldung
$error .= "Geben Sie bitte einen korrekten Nachnamen ein.<br />";
}

// emailadresse vorhanden, mindestens 1 Zeichen und maximal 100 zeichen lang
if(isset($_POST['email']) && !empty(trim($_POST['email'])) && strlen(trim($_POST['email'])) <= 100){
$email = htmlspecialchars(trim($_POST['email']));
// korrekte emailadresse?
if (filter_var($email, FILTER_VALIDATE_EMAIL) === false){
$error .= "Geben Sie bitte eine korrekte Email-Adresse ein<br />";
}
} else {
// Ausgabe Fehlermeldung
$error .= "Geben Sie bitte eine korrekte Email-Adresse ein.<br />";
}

// benutzername vorhanden, mindestens 6 Zeichen und maximal 30 zeichen lang
if(isset($_POST['username']) && !empty(trim($_POST['username'])) && strlen(trim($_POST['username'])) <= 30){
$username = trim($_POST['username']);
// entspricht der benutzername unseren vogaben (minimal 6 Zeichen, Gross- und Kleinbuchstaben)
if(!preg_match("/(?=.*[a-z])(?=.*[A-Z])[a-zA-Z]{6,}/", $username)){
$error .= "Der Benutzername entspricht nicht dem geforderten Format.<br />";
}
} else {
// Ausgabe Fehlermeldung
$error .= "Geben Sie bitte einen korrekten Benutzernamen ein.<br />";
}

// passwort vorhanden, mindestens 8 Zeichen
if(isset($_POST['password']) && !empty(trim($_POST['password']))){
$password = trim($_POST['password']);
//entspricht das passwort unseren vorgaben? (minimal 8 Zeichen, Zahlen, Buchstaben, keine Zeilenumbrüche, mindestens ein Gross- und ein Kleinbuchstabe)
if(!preg_match("/(?=^.{8,}$)((?=.*\d+)(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/", $password)){
$error .= "Das Passwort entspricht nicht dem geforderten Format.<br />";
}
} else {
// Ausgabe Fehlermeldung
$error .= "Geben Sie bitte einen korrekten Nachnamen ein.<br />";
}

// wenn kein Fehler vorhanden ist, schreiben der Daten in die Datenbank
if(empty($error)){
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
}else{

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