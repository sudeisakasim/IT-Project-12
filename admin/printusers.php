<?php
include '../components/connect.php';

if(isset($_COOKIE['admin_id'])){
   $admin_id = $_COOKIE['admin_id'];
}else{
   $admin_id = '';
   header('location:login.php');
}

if(isset($_POST['delete'])){
   // ... (your delete logic)
}






if(isset($_POST['delete'])){

    $delete_id = $_POST['delete_id'];
    $delete_id = filter_var($delete_id, FILTER_SANITIZE_STRING);
 
    $verify_delete = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
    $verify_delete->execute([$delete_id]);
 
    if($verify_delete->rowCount() > 0){
       $select_images = $conn->prepare("SELECT * FROM `property` WHERE user_id = ?");
       $select_images->execute([$delete_id]);
       while($fetch_images = $select_images->fetch(PDO::FETCH_ASSOC)){
          $image_01 = $fetch_images['image_01'];
          $image_02 = $fetch_images['image_02'];
          $image_03 = $fetch_images['image_03'];
          $image_04 = $fetch_images['image_04'];
          $image_05 = $fetch_images['image_05'];
          unlink('../uploaded_files/'.$image_01);
          if(!empty($image_02)){
             unlink('../uploaded_files/'.$image_02);
          }
          if(!empty($image_03)){
             unlink('../uploaded_files/'.$image_03);
          }
          if(!empty($image_04)){
             unlink('../uploaded_files/'.$image_04);
          }
          if(!empty($image_05)){
             unlink('../uploaded_files/'.$image_05);
          }
       }
       $delete_listings = $conn->prepare("DELETE FROM `property` WHERE user_id = ?");
       $delete_listings->execute([$delete_id]);
       $delete_requests = $conn->prepare("DELETE FROM `requests` WHERE sender = ? OR receiver = ?");
       $delete_requests->execute([$delete_id, $delete_id]);
       $delete_saved = $conn->prepare("DELETE FROM `saved` WHERE user_id = ?");
       $delete_saved->execute([$delete_id]);
       $delete_user = $conn->prepare("DELETE FROM `users` WHERE id = ?");
       $delete_user->execute([$delete_id]);
       $success_msg[] = 'user deleted!';
    }else{
       $warning_msg[] = 'User deleted already!';
    }
 
 }



 



?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Users</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/admin_style.css">

   <style>
      #customers {
        font-family: Arial, Helvetica, sans-serif;
         border-collapse: collapse;
         width: 190%; /* Adjust the width as needed */
         margin: 0; /* Remove auto margin to center */
         margin-left: -45%; /* Adjust the left margin as needed */
      }

      #customers td, #customers th {
         border: 1px solid #ddd;
         padding: 8px;
         font-size: 16px; /* Increase font size for details */
      }

      #customers th {
         padding-top: 12px;
         padding-bottom: 12px;
         text-align: left;
         background-color: #04AA6D;
         color: white;
      }

      #customers tr:nth-child(even){
         background-color: #f2f2f2;
      }

      #customers tr:hover {
         background-color: #ddd;
      }

      .empty {
         text-align: center;
      }

      .delete-btn {
         padding: 5px 10px;
         background-color: #FF4136;
         border: none;
         color: white;
         cursor: pointer;
         font-size: 14px; /* Keep the delete button font size smaller */
      }

      .delete-btn:hover {
         background-color: #E11C17;
      }

      .print-button-container {
         display: flex;
         justify-content: space-between;
         align-items: flex-end;
         margin-top: 5px; /* Adjust vertical position */
      }

      .print-button {
         background-color: #3498db;
         color: white;
         padding: 10px 20px;
         border: none;
         cursor: pointer;
         font-size: 16px;
         border-radius: 4px;
      }

      .print-button:hover {
         background-color: #2980b9;
      }
   </style>
</head>
<body>
   
<!-- header section starts  -->
<?php include '../components/admin_header.php'; ?>
<!-- header section ends -->

<!-- admins section starts  -->

<section class="grid">

   <h1 class="heading">Users</h1>

   <div class="print-button-container">
      <button class="print-button" onclick="window.print()">Print Table</button>
   </div>

   <form action="" method="POST" class="search-form">
      <input type="text" name="search_box" placeholder="Search users..." maxlength="100" required>
      <button type="submit" class="fas fa-search" name="search_btn"></button>
   </form>

   <div class="box-container">
      <table id="customers">
         <thead>
            <tr>
               <th>#</th>
               <th>Name</th>
               <th>Number</th>
               <th>Email</th>
               <th>Properties Listed</th>
               <th>Action</th>
            </tr>
         </thead>
         <tbody>
            <?php
            $rowNumber = 1;
            if(isset($_POST['search_btn'])){
               $search_box = $_POST['search_box'];
               $search_box = filter_var($search_box, FILTER_SANITIZE_STRING);
               $select_users = $conn->prepare("SELECT * FROM `users` WHERE name LIKE '%$search_box%' OR number LIKE '%$search_box%' OR email LIKE '%$search_box%'");
               $select_users->execute();
            } else {
               $select_users = $conn->prepare("SELECT * FROM `users`");
               $select_users->execute();
            }

            if($select_users->rowCount() > 0){
               while($fetch_users = $select_users->fetch(PDO::FETCH_ASSOC)){
                  // Count properties for the user
                  $count_property = $conn->prepare("SELECT * FROM `property` WHERE user_id = ?");
                  $count_property->execute([$fetch_users['id']]);
                  $total_properties = $count_property->rowCount();

                  echo '<tr>';
                  echo '<td>' . $rowNumber . '</td>';
                  echo '<td>' . $fetch_users['name'] . '</td>';
                  echo '<td><a href="tel:' . $fetch_users['number'] . '">' . $fetch_users['number'] . '</a></td>';
                  echo '<td><a href="mailto:' . $fetch_users['email'] . '">' . $fetch_users['email'] . '</a></td>';
                  echo '<td>' . $total_properties . '</td>';
                  echo '<td>';
                  echo '<form action="" method="POST">';
                  echo '<input type="hidden" name="delete_id" value="' . $fetch_users['id'] . '">';
                  echo '<button type="submit" onclick="return confirm(\'Delete this user?\');" name="delete" class="delete-btn">Delete User</button>';
                  echo '</form>';
                  echo '</td>';
                  echo '</tr>';

                  $rowNumber++;
               }
            }
            elseif(isset($_POST['search_btn'])){
               echo '<tr><td colspan="6" class="empty">No results found!</td></tr>';
            }
            else{
               echo '<tr><td colspan="6" class="empty">No user accounts added yet!</td></tr>';
            }
            ?>
         </tbody>
      </table>
   </div>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script src="../js/admin_script.js"></script>
<?php include '../components/message.php'; ?>

</body>
</html>
