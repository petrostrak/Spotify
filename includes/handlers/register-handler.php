<?php 

function sanitizeFormUsername($inputText){
    $inputText = strip_tags($inputText); // removes all html elements
    $inputText = str_replace(" ", "", $inputText); // removes spacing
    return $inputText;
}

function sanitizeFormString($inputText){
    $inputText = sanitizeFormUsername($inputText);
    $inputText = ucfirst(strtolower($inputText)); // first we convert the string value toLowerCase with strtoLower and then only the first letter to upperCase with ucfirst
    return $inputText;
}

function sanitizeFormPassword($inputText){
    $inputText = strip_tags($inputText);
    return $inputText;
}

function validateUsername($un){

}

function validateFirstName($fn){

}

function validateLastName($ln){

}

function validateEmails($em, em2){

}

function validatePasswords($pw, $pw2){

}

if (isset($_POST['registerButton'])) {
    $username = sanitizeFormUsername($_POST['username']);
    $firstName = sanitizeFormString($_POST['firstName']);
    $lastName = sanitizeFormString($_POST['lastName']);
    $email = sanitizeFormString($_POST['email']);
    $email2 = sanitizeFormString($_POST['email2']);
    $password = sanitizeFormPassword($_POST['password']);
    $password2 = sanitizeFormPassword($_POST['password2']);

    validateUsername($username);
    validateFirstName($firstName);
    validateLastName($lastName);
    validateEmails($email, $email2);
    validatePasswords($password, $password2);

}

?>