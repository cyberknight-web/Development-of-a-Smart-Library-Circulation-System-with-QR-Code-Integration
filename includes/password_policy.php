<?php
// Shared password policy validation.

function smartlibrary_password_policy_message(): string
{
    return 'Password must be at least 8 characters long and contain at least one uppercase letter, one number, and one symbol.';
}

function smartlibrary_is_valid_password(string $password): bool
{
    return preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/', $password) === 1;
}
