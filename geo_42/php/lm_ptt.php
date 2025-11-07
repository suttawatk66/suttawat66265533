<?php
header('Content-Type: application/json');

$data = [
  "type" => "FeatureCollection",
  "features" => [
    ["type"=>"Feature","geometry"=>["type"=>"Point","coordinates"=>[100.293,16.824]],"properties"=>["id"=>1,"timestamp"=>"2025-09-29 10:00:00","name"=>"ปั๊ม PTT 1","type"=>"fuel","prov_namt"=>"พิษณุโลก"]],
    ["type"=>"Feature","geometry"=>["type"=>"Point","coordinates"=>[100.289,16.825]],"properties"=>["id"=>2,"timestamp"=>"2025-09-29 10:10:00","name"=>"ปั๊ม PTT 2","type"=>"fuel","prov_namt"=>"พิษณุโลก"]],
    ["type"=>"Feature","geometry"=>["type"=>"Point","coordinates"=>[100.287,16.828]],"properties"=>["id"=>3,"timestamp"=>"2025-09-29 10:20:00","name"=>"ปั๊ม PTT 3","type"=>"fuel","prov_namt"=>"พิษณุโลก"]],
    ["type"=>"Feature","geometry"=>["type"=>"Point","coordinates"=>[100.290,16.829]],"properties"=>["id"=>4,"timestamp"=>"2025-09-29 10:30:00","name"=>"ปั๊ม PTT 4","type"=>"fuel","prov_namt"=>"พิษณุโลก"]],
    ["type"=>"Feature","geometry"=>["type"=>"Point","coordinates"=>[100.292,16.827]],"properties"=>["id"=>5,"timestamp"=>"2025-09-29 10:40:00","name"=>"ปั๊ม PTT 5","type"=>"fuel","prov_namt"=>"พิษณุโลก"]]
  ]
];

echo json_encode($data);
