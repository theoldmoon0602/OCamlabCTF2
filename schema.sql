create table if not exists problems (
  id integer primary key,
  title text unique not null,
  description text not null,
  point integer not null,
  flag text not null
);

create table if not exists users (
  id integer primary key,
  username text unique not null,
  password_hash text not null,
  icon text,
  is_admin integer not null default 0
);

create table if not exists activities (
  id integer primary key,
  user_id text not null,
  problem_id integer not null,
  activity_type integer not null,
  got_score integer not null,
  created_at integer not null
);

create table if not exists competition (
  name text not null,
  start_at integer not null,
  end_at integer not null,
  enabled integer not null default 0
);
