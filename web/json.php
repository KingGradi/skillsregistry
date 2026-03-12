<?php  
//PDO is a extension which  defines a lightweight, consistent interface for accessing databases in PHP.  
$db=new PDO('mysql:dbname=ispa;host=localhost;','ispa','qwerty9');  
//here prepare the query for analyzing, prepared statements use less resources and thus run faster  
$row=$db->prepare('select id,fullname from members where member_status="G" and memtype_v2 !="N" order by fullname');  
  
$row->execute();//execute the query  
$json_data=array();//create the array  
foreach($row as $rec)//foreach loop  
{  
//$json_array['id']=$rec['id'];  
    $json_array['name']=$rec['fullname'];  
//    $json_array['roll_no']=$rec['roll_no'];  
//    $json_array['degree']=$rec['degree'];  
//here pushing the values in to an array  
    array_push($json_data,$json_array);  
  
}  
  
//built in PHP function to encode the data in to JSON format  
echo json_encode($json_data);  
  
  
?>
