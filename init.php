<?php

require_once './vendor/autoload.php';

unlink('database/database.db');
mkdir('database');
system('sqlite3 database/database.db < schema.sql');
system('sudo chown -R www-data:user database/');
system('sudo chmod -R 0775 database/');

$medoo = new \Medoo\Medoo([
  'database_type' => 'sqlite',
  'database_file' => __DIR__ . '/database/database.db',
  'option'        => [
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
  ]
]);

// insert admin
$medoo->insert("users", [
  "username" => "admin",
  "password_hash" => password_hash("mogumogutakoyaki", PASSWORD_DEFAULT),
  "is_admin" => 1
]);

// insert competition info
$medoo->insert("competition", [
  "name" => "OCamlab CTF #2",
  "start_at" => 1535554800,
  "end_at" => 1535598000,
  "enabled" => 1
]);

// insert problems
$medoo->insert('problems', [
  "title" => "Knock Knock",
  "description" => "http://<Server IP>/assets/knock.py",
  "point" => 100,
  "flag" => "OCamlab{ICMP_can_send_data}"
]);

// insert problems
$medoo->insert('problems', [
  "title" => "Microblog",
  "description" => "http://<Server IP>:8000/  The Attack keyword is password of administrator. Defense keyword should be included in recent 10 posts of admin.",
  "point" => 200,
  "flag" => "OCamlab{Blind_XXX_Injection}"
]);

// insert problems
$medoo->insert('problems', [
  "title" => "Crypto + Crypto",
  "description" => "<Server IP> 8888  http://<Server IP>/assets/paillier.py",
  "point" => 300,
  "flag" => "OCamlab{Pailler_Crypto_has_Homomorphism}"
]);

file_put_contents("flag_keyword", "ocamlab");
system('sudo chmod 0775 flag_keyword');
