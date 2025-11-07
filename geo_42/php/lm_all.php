<?php
header('Content-Type: application/json');

$data = [
  "type" => "FeatureCollection",
  "features" => [

    // ===== 7-Eleven (5 จุด) =====
    ["type"=>"Feature","geometry"=>["type"=>"Point","coordinates"=>[100.292,16.825]],"properties"=>["id"=>101,"timestamp"=>"2025-09-29 08:00:00","name"=>"7-Eleven สาขา 1","type"=>"convenience","prov_namt"=>"พิษณุโลก"]],
    ["type"=>"Feature","geometry"=>["type"=>"Point","coordinates"=>[100.288,16.823]],"properties"=>["id"=>102,"timestamp"=>"2025-09-29 08:10:00","name"=>"7-Eleven สาขา 2","type"=>"convenience","prov_namt"=>"พิษณุโลก"]],
    ["type"=>"Feature","geometry"=>["type"=>"Point","coordinates"=>[100.286,16.827]],"properties"=>["id"=>103,"timestamp"=>"2025-09-29 08:20:00","name"=>"7-Eleven สาขา 3","type"=>"convenience","prov_namt"=>"พิษณุโลก"]],
    ["type"=>"Feature","geometry"=>["type"=>"Point","coordinates"=>[100.290,16.828]],"properties"=>["id"=>104,"timestamp"=>"2025-09-29 08:30:00","name"=>"7-Eleven สาขา 4","type"=>"convenience","prov_namt"=>"พิษณุโลก"]],
    ["type"=>"Feature","geometry"=>["type"=>"Point","coordinates"=>[100.291,16.826]],"properties"=>["id"=>105,"timestamp"=>"2025-09-29 08:40:00","name"=>"7-Eleven สาขา 5","type"=>"convenience","prov_namt"=>"พิษณุโลก"]],

    // ===== วัด (5 จุด) =====
    ["type"=>"Feature","geometry"=>["type"=>"Point","coordinates"=>[100.292,16.825]],"properties"=>["id"=>201,"timestamp"=>"2025-09-29 09:00:00","name"=>"วัดพระศรีรัตนมหาธาตุ","type"=>"place_of_worship","prov_namt"=>"พิษณุโลก"]],
    ["type"=>"Feature","geometry"=>["type"=>"Point","coordinates"=>[100.285,16.827]],"properties"=>["id"=>202,"timestamp"=>"2025-09-29 09:10:00","name"=>"วัดราชบูรณะ","type"=>"place_of_worship","prov_namt"=>"พิษณุโลก"]],
    ["type"=>"Feature","geometry"=>["type"=>"Point","coordinates"=>[100.291,16.819]],"properties"=>["id"=>203,"timestamp"=>"2025-09-29 09:20:00","name"=>"วัดนางพญา","type"=>"place_of_worship","prov_namt"=>"พิษณุโลก"]],
    ["type"=>"Feature","geometry"=>["type"=>"Point","coordinates"=>[100.296,16.822]],"properties"=>["id"=>204,"timestamp"=>"2025-09-29 09:30:00","name"=>"วัดจันทร์ประดิษฐาราม","type"=>"place_of_worship","prov_namt"=>"พิษณุโลก"]],
    ["type"=>"Feature","geometry"=>["type"=>"Point","coordinates"=>[100.288,16.821]],"properties"=>["id"=>205,"timestamp"=>"2025-09-29 09:40:00","name"=>"วัดมหาธาตุ","type"=>"place_of_worship","prov_namt"=>"พิษณุโลก"]],

    // ===== PTT (5 จุด) =====
    ["type"=>"Feature","geometry"=>["type"=>"Point","coordinates"=>[100.293,16.824]],"properties"=>["id"=>301,"timestamp"=>"2025-09-29 10:00:00","name"=>"ปั๊ม PTT 1","type"=>"fuel","prov_namt"=>"พิษณุโลก"]],
    ["type"=>"Feature","geometry"=>["type"=>"Point","coordinates"=>[100.289,16.825]],"properties"=>["id"=>302,"timestamp"=>"2025-09-29 10:10:00","name"=>"ปั๊ม PTT 2","type"=>"fuel","prov_namt"=>"พิษณุโลก"]],
    ["type"=>"Feature","geometry"=>["type"=>"Point","coordinates"=>[100.287,16.828]],"properties"=>["id"=>303,"timestamp"=>"2025-09-29 10:20:00","name"=>"ปั๊ม PTT 3","type"=>"fuel","prov_namt"=>"พิษณุโลก"]],
    ["type"=>"Feature","geometry"=>["type"=>"Point","coordinates"=>[100.290,16.829]],"properties"=>["id"=>304,"timestamp"=>"2025-09-29 10:30:00","name"=>"ปั๊ม PTT 4","type"=>"fuel","prov_namt"=>"พิษณุโลก"]],
    ["type"=>"Feature","geometry"=>["type"=>"Point","coordinates"=>[100.292,16.827]],"properties"=>["id"=>305,"timestamp"=>"2025-09-29 10:40:00","name"=>"ปั๊ม PTT 5","type"=>"fuel","prov_namt"=>"พิษณุโลก"]]
  ]
];

echo json_encode($data);
