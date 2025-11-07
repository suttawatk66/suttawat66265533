<?php
header('Content-Type: application/json');

$data = [
  "type" => "FeatureCollection",
  "features" => [
    ["type"=>"Feature","geometry"=>["type"=>"Point","coordinates"=>[100.292,16.825]],"properties"=>["id"=>1,"timestamp"=>"2025-09-29 08:00:00","name"=>"7-Eleven สาขา 1","type"=>"convenience","prov_namt"=>"พิษณุโลก"]],
    ["type"=>"Feature","geometry"=>["type"=>"Point","coordinates"=>[100.288,16.823]],"properties"=>["id"=>2,"timestamp"=>"2025-09-29 08:10:00","name"=>"7-Eleven สาขา 2","type"=>"convenience","prov_namt"=>"พิษณุโลก"]],
    ["type"=>"Feature","geometry"=>["type"=>"Point","coordinates"=>[100.286,16.827]],"properties"=>["id"=>3,"timestamp"=>"2025-09-29 08:20:00","name"=>"7-Eleven สาขา 3","type"=>"convenience","prov_namt"=>"พิษณุโลก"]],
    ["type"=>"Feature","geometry"=>["type"=>"Point","coordinates"=>[100.290,16.828]],"properties"=>["id"=>4,"timestamp"=>"2025-09-29 08:30:00","name"=>"7-Eleven สาขา 4","type"=>"convenience","prov_namt"=>"พิษณุโลก"]],
    ["type"=>"Feature","geometry"=>["type"=>"Point","coordinates"=>[100.291,16.826]],"properties"=>["id"=>5,"timestamp"=>"2025-09-29 08:40:00","name"=>"7-Eleven สาขา 5","type"=>"convenience","prov_namt"=>"พิษณุโลก"]]
  ]
];

echo json_encode($data);
