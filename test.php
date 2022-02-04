<div class='container'>
    <h1>Registrierung</h1>

    <form action='' method='post'>
        <!-- benutzername -->
        <div class='form-group'>
            <label for='username'>Username *</label>
            <input type='text' name='username' class='form-control' id='username'
                   placeholder='Username'
                   maxlength='30' required>
        </div>
        <!-- vorname -->
        <div class='form-group'>
            <label for='firstname'>First Name *</label>
            <input type='text' name='firstname' class='form-control' id='firstname'
                   placeholder='First Name'
                   required>
        </div>
        <!-- nachname -->
        <div class='form-group'>
            <label for='lastname'>Last Name *</label>
            <input type='text' name='lastname' class='form-control' id='lastname'
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
            <input type='checkbox' class='form-check-input'>
            <label class='form-check-label' for='exampleCheck1' id='status'>Admin</label>
        </div>
        <!-- categories -->
        <div class='form-group form-check'>