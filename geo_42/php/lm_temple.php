<?php
header('Content-Type: application/json');

$data = [
  "type" => "FeatureCollection",
  "features" => [
    ["type"=>"Feature","geometry"=>["type"=>"Point","coordinates"=>[100.292,16.825]],"properties"=>["id"=>1,"timestamp"=>"2025-09-29 09:00:00","name"=>"วัดพระศรีรัตนมหาธาตุ","type"=>"place_of_worship","prov_namt"=>"พิษณุโลก"]],
    ["type"=>"Feature","geometry"=>["type"=>"Point","coordinates"=>[100.285,16.827]],"properties"=>["id"=>2,"timestamp"=>"2025-09-29 09:10:00","name"=>"วัดราชบูรณะ","type"=>"place_of_worship","prov_namt"=>"พิษณุโลก"]],
    ["type"=>"Feature","geometry"=>["type"=>"Point","coordinates"=>[100.291,16.819]],"properties"=>["id"=>3,"timestamp"=>"2025-09-29 09:20:00","name"=>"วัดนางพญา","type"=>"place_of_worship","prov_namt"=>"พิษณุโลก"]],
    ["type"=>"Feature","geometry"=>["type"=>"Point","coordinates"=>[100.296,16.822]],"properties"=>["id"=>4,"timestamp"=>"2025-09-29 09:30:00","name"=>"วัดจันทร์ประดิษฐาราม","type"=>"place_of_worship","prov_namt"=>"พิษณุโลก"]],
    ["type"=>"Feature","geometry"=>["type"=>"Point","coordinates"=>[100.288,16.821]],"properties"=>["id"=>5,"timestamp"=>"2025-09-29 09:40:00","name"=>"วัดมหาธาตุ","type"=>"place_of_worship","prov_namt"=>"พิษณุโลก"]]
  ]
];

echo json_encode($data);
