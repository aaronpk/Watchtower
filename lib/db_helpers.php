<?php
namespace db;
use \ORM;

function now() {
  return date('Y-m-d H:i:s');
}

function set_updated(&$record) {
  $record->updated_at = date('Y-m-d H:i:s');
}

function find_or_create($table, $where, $defaults=[], $autosave=false) {
  $item = ORM::for_table($table);

  // Where is an associative array of key/val combos
  foreach($where as $c=>$v) {
    $item = $item->where($c, $v);
  }

  $item = $item->find_one();

  if(!$item) {
    $item = ORM::for_table($table)->create();
    $item->created_at = date('Y-m-d H:i:s');
    foreach($defaults as $k=>$v) {
      $item->{$k} = $v;
    }
    foreach($where as $k=>$v) {
      $item->{$k} = $v;
    }
    if($autosave)
      $item->save();
  }
  return $item;
}

function find($table, $where) {
  $item = ORM::for_table($table);

  // Where is an associative array of key/val combos
  foreach($where as $c=>$v) {
    $item = $item->where($c, $v);
  }

  return $item->find_one();
}

function create($table, $data) {
  $item = ORM::for_table($table)->create();
  foreach($data as $k=>$v) {
    $item->{$k} = $v;
  }
  $item->save();
  return $item;
}

function feed_from_url($url) {
  return ORM::for_table('feeds')->where('url', $url)->find_one();
}

function get_by_id($table, $id) {
  return ORM::for_table($table)->where('id', $id)->find_one();
}

function get_by_col($table, $col, $val) {
  return ORM::for_table($table)->where($col, $val)->find_one();
}
