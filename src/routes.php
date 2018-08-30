<?php

use Slim\Http\Request;
use Slim\Http\Response;

date_default_timezone_set("Asia/Tokyo");

$authorization = function ($admin_only = false) use ($container) {
  return function ($request, $response, $next) use ($admin_only, $container) {
    if ($admin_only && $_SESSION['is_admin'] == 0) {
      $container['flash']->addMessage('errors', 'Administrator pemission is required');
      return $response->withRedirect($this->router->pathFor('login'));
    }

    if (!isset($_SESSION['user'])) {
      $container['flash']->addMessage('errors', 'Login required');
      return $response->withRedirect($this->router->pathFor('login'));
    }

    return $next($request, $response);
  };
};
$time_range = function($request, $response, $next) use ($container) {
  $competition = $container['db']->select('competition', '*')[0];
  $now = time();

  if (!$competition['enabled'] || $now < $competition['start_at'] || $competition['end_at'] < $now) {
    $container['flash']->addMessage('errors', 'Contest outdated');
    return $response->withRedirect($this->router->pathFor('index'));
  }
  return $next($request, $response);
};

$app->get('/', function (Request $request, Response $response, array $args) {
  $r = $this->db->select('competition', '*')[0];
  $s = new DateTime();
  $s->setTimestamp($r['start_at']);
  $r['start_at'] = $s->format('Y-m-d H:i:s');
  $s->setTimestamp($r['end_at']);
  $r['end_at'] = $s->format('Y-m-d H:i:s');
  return $this->view->render($response, 'index.html', $r);
})->setName('index');

$app->get('/login', function (Request $request, Response $response, array $args) {
  return $this->view->render($response, 'login.html');
})->setName('login');

$app->post('/login', function (Request $request, Response $response, array $args) use ($app) {
  $postParams = $request->getParsedBody();

  // check required paramter
  if (!isset($postParams['username'])) {
    $this->flash->addMessage('errors', 'Parameter username is required');
    return $this->view->render($response, 'login.html');
  }
  if (!isset($postParams['password'])) {
    $this->flash->addMessage('errors', 'Parameter password is required');
    return $this->view->render($response, 'login.html');
  }
  if (!isset($postParams['login']) && !isset($postParams['register'])) {
    $this->flash->addMessage('errors', 'Parameter either login or regsiter is required');
    return $this->view->render($response, 'login.html');
  }

  $username = $postParams['username'];
  $password = $postParams['password'];
  $is_login = isset($postParams['login']);

  // assertion username
  if (mb_strlen($username) > 20) {
    $this->flash->addMessage('errors', 'Length of paramter username must be in range 1 to 20');
    return $this->view->render($response, 'login.html');
  }
  if (! preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $username)) {
    $this->flash->addMessage('errors', 'Parameter username must match with regular expression [A-Za-z][A-Za-z0-9_]');
    return $this->view->render($response, 'login.html');
  }

  if ($is_login) {
    $users = $this->db->select("users", ["id", "password_hash", "is_admin"], [
      'username' => $username
    ]);

    if (count($users) != 1) {
      $this->flash->addMessage('errors', "User $username does not exist." . var_export($users, true));
      return $this->view->render($response, 'login.html');
    }

    $hash = $users[0]['password_hash'];
    if (! password_verify($password, $hash)) {
      $this->flash->addMessage('errors', "Password for user $username does not correct.");
      return $this->view->render($response, 'login.html');
    }
    $_SESSION['user'] = $username;
    $_SESSION['user_id'] = $users[0]["id"];
    $_SESSION['is_admin'] = $users[0]["is_admin"];

    $this->flash->addMessage('messages', 'Hello '.$username);
    return $response->withRedirect($this->router->pathFor('index'));
  }
  else {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    try {
      $this->db->insert("users", [
        "username" => $username,
        "password_hash" => $hash,
      ]);
    }
    catch (PDOException $e) {
      $this->flash->addMessage('errors', "The user $username is already exist");
      return $response->withRedirect($this->router->pathFor('login'));
    }

    $this->flash->addMessage('messages', 'Register succeeded.');
    return $response->withRedirect($this->router->pathFor('login'));
  }
});

$app->post('/logout', function (Request $request, Response $response, array $args) {
  unset($_SESSION['user']);
  unset($_SESSION['user_id']);
  unset($_SESSION['is_admin']);
  return $this->view->render($response, 'login.html');
})->setName('login');

$app->get('/problems', function (Request $request, Response $response, array $args) {
  $problems = $this->db->query('select id, title, point, case when exists (select id from activities where problem_id = problems.id and user_id = :user_id and activity_type = 1) then 1 else 0 end as solved from problems', [
    ':user_id' => $_SESSION['user_id'],
  ])->fetchAll();
  return $this->view->render($response, 'problems.html', [
    'problems' => $problems
  ]);
})->setName('problems')->add($authorization());

$app->get('/problem/{id}', function (Request $request, Response $response, array $args) {
  $id = $args['id'];
  try {
    $problem = $this->db->select('problems', ['id', 'title', 'point', 'description'], [
      'id' => $id
    ])[0];
    $solved = $this->db->select('activities', ['count(*)'], [
      'problem_id' => $id,
      'user_id' => $_SESSION['user_id'],
      'activity_type' => 1
    ])[0];
    $problem['solved'] = $solved;
  }
  catch (Exception $e) {
    $this->flash->addMessage('errors', 'Problem not found');
    return $response->withRedirect($this->router->pathFor('problems'));
  }

  return $this->view->render($response, 'problem.html', [
    'p' => $problem
  ]);
})->add($authorization())->add($time_range);

$app->post('/problem/{id}', function (Request $request, Response $response, array $args) {
  $id = $args['id'];
  $postParams = $request->getParsedBody();
  if (!isset($postParams['flag'])) {
    $this->flash->addMessage('errors', 'Parameter flag is required');
    return $response->withRedirect('/problem/'.$id);
  }

  try {
    $problem = $this->db->select('problems', ['flag', 'point'], [
      'id' => $id
    ])[0];
    $solved = $this->db->select('activities', ['count(*)'], [
      'problem_id' => $id,
      'user_id' => $_SESSION['user_id'],
      'activity_type' => 1
    ])[0];

    if ($solved) {
      $this->flash->addMessage('messages', 'You already solved this problem.');

      if ($problem['flag'] === $postParams['flag']) {
        $this->flash->addMessage('messages', 'Correct!!!');
      }
      else {
        $this->flash->addMessage('errors', 'Wrong...');
      }

      return $response->withRedirect('/problem/'.$id);
    }

    $activity_type = null;
    $got_score = null;

    if ($problem['flag'] === $postParams['flag']) {

      $this->flash->addMessage('messages', 'Correct!!!');
      $got_score = $problem['point'];
      $activity_type = 1;
    }
    else {
      $this->flash->addMessage('errors', 'Wrong...');
      $got_score = 0;
      $activity_type = 0;
    }

    $this->db->insert('activities', [
      'user_id' => $_SESSION['user_id'],
      'problem_id' => $id,
      'activity_type' => $activity_type,
      'got_score' => $got_score,
      'created_at' => time()
    ]);

    return $response->withRedirect('/problem/'.$id);
  }
  catch (Exception $e) {
    $this->flash->addMessage('errors', 'Problem not found');
    return $response->withRedirect('/problem/'.$id);
  }

  return $this->view->render($response, 'problem.html', [
    'p' => $problem
  ]);
})->add($authorization())->add($time_range);

$app->get('/score.json', function (Request $request, Response $response, array $args) {
  $activities = $this->db->query('select username, got_score, created_at from activities inner join users on user_id = users.id inner join problems on problem_id = problems.id where got_score != 0 order by created_at asc')->fetchAll();

  $scores = [];

});
$app->get('/scores', function (Request $request, Response $response, array $args) {
  $scores = $this->db->query('select user_id, sum(got_score) as score, username from activities inner join users on user_id = users.id group by user_id, username order by sum(got_score) desc')->fetchAll();

  $activities = $this->db->query('select username, title, got_score, activity_type, created_at from activities inner join users on user_id = users.id inner join problems on problem_id = problems.id order by created_at desc limit 20')->fetchAll();

  return $this->view->render($response, 'scores.html', [
    'scores' => $scores,
    'activities' => $activities
  ]);
});


$app->group('/admin', function() use ($app, $container) {
  $app->get('', function (Request $request, Response $response, array $args) {
    return $this->view->render($response, 'admin.html');
  });
  $app->get('/competition', function (Request $request, Response $response, array $args) {
    $r = $this->db->select('competition', '*')[0];
    $s = new DateTime();
    $s->setTimestamp($r['start_at']);
    $r['start_at'] = $s->format('Y-m-d') . 'T' . $s->format('H:i');
    $s->setTimestamp($r['end_at']);
    $r['end_at'] = $s->format('Y-m-d') . 'T' . $s->format('H:i');

    return $this->view->render($response, 'admin/competition.html', $r);
  })->setName('comp');
  $app->post('/competition', function (Request $request, Response $response, array $args) {
    $postParams = $request->getParsedBody();

    // check required paramter
    if (!isset($postParams['start_at'])) {
      $this->flash->addMessage('errors', 'Parameter start_at is required');
      return $response->withRedirect($this->router->pathFor('comp'));
    }
    if (!isset($postParams['end_at'])) {
      $this->flash->addMessage('errors', 'Parameter end_at is required');
      return $response->withRedirect($this->router->pathFor('comp'));
    }

    try {
      $start_at = (new DateTime($postParams['start_at']))->getTimestamp();
      $end_at = (new DateTime($postParams['end_at']))->getTimestamp();
      $enabled = isset($postParams['enabled']);

      if ($start_at >= $end_at) {
        $this->flash->addMessage('errors', 'Restriction: start_at < end_at');
        return $response->withRedirect($this->router->pathFor('comp'));
      }

      $this->db->update('competition', [
        'start_at' => $start_at,
        'end_at' => $end_at,
        'enabled' => $enabled,
      ]);

      return $response->withRedirect($this->router->pathFor('comp'));
    }
    catch (Exception $e) {
      $this->flash->addMessage('errors', $e);
      return $response->withRedirect($this->router->pathFor('comp'));
    }

  });
})->add($authorization(true));
