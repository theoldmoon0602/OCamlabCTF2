<?php

require_once './vendor/autoload.php';

$medoo = new \Medoo\Medoo([
  'database_type' => 'sqlite',
  'database_file' => __DIR__ . '/database/database.db',
  'option'        => [
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
  ]
]);

$users = $medoo->select("users", ["id", "username"]);

$m = trim(file_get_contents("flag_keyword"));

exec('echo | nc localhost 6000', $a); // knock knock
exec('echo | nc localhost 6001', $b); // Microblog
exec('echo | nc localhost 6002', $c); // Crypto + Crypto
$kings = [
  implode("\n", $a),
  implode("\n", $b),
  implode("\n", $c),
];

foreach ($users as $u) {
  $l = $u["username"].$m;
  for ($i = 0; $i < count($kings); $i++) {
    if (strpos($kings[$i], $l) !== FALSE) {
      $medoo->insert("activities", [
        "user_id" => $u["id"],
        "problem_id" => $i + 1,
        "activity_type" => 2,  // defense point
        "got_score" => 10,
        "created_at" => time()
      ]);
    }
  }
}

function random($length)
{
    return substr(bin2hex(random_bytes($length)), 0, $length);
}
file_put_contents("flag_keyword", random(16) . "\n");
