<?php
include '../components/connect.php';

if(isset($_COOKIE['admin_id'])){
   $admin_id = $_COOKIE['admin_id'];
}else{
   $admin_id = '';
   header('location:login.php');
}

if(isset($_POST['delete'])){
   $delete_id = $_POST['delete_id'];
   $delete_id = filter_var($delete_id, FILTER_SANITIZE_STRING);

   $verify_delete = $conn->prepare("SELECT * FROM `property` WHERE id = ?");
   $verify_delete->execute([$delete_id]);

   if($verify_delete->rowCount() > 0){
      $select_images = $conn->prepare("SELECT * FROM `property` WHERE id = ?");
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
      $delete_listings = $conn->prepare("DELETE FROM `property` WHERE id = ?");
      $delete_listings->execute([$delete_id]);
      $success_msg[] = 'Listing deleted!';
   }else{
      $warning_msg[] = 'Listing deleted already!';
   }
}

$select_listings = null; // Initialize the variable

if(isset($_POST['search_btn'])){
   $search_box = $_POST['search_box'];
   $search_box = filter_var($search_box, FILTER_SANITIZE_STRING);
   $select_listings = $conn->prepare("SELECT * FROM `property` WHERE property_name LIKE '%$search_box%' OR address LIKE '%$search_box%' ORDER BY date DESC");
   $select_listings->execute();
} else {
   $select_listings = $conn->prepare("SELECT * FROM `property` ORDER BY date DESC");
   $select_listings->execute();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Listings</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/admin_style.css">

   <style>
      #listings {
        font-family: Arial, Helvetica, sans-serif;
         border-collapse: collapse;
         width: 190%; /* Adjust the width as needed */
         margin: 0; /* Remove auto margin to center */
         margin-left: -45%; /* Adjust the left margin as needed */
      }

      #listings td, #listings th {
        border: 1px solid #ddd;
        padding: 8px;
        font-size: 16px;
      }

      #listings th {
        padding-top: 12px;
        padding-bottom: 12px;
        text-align: left;
        background-color: #04AA6D;
        color: white;
      }

      #listings tr:nth-child(even){
        background-color: #f2f2f2;
      }

      #listings tr:hover {
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
        font-size: 14px;
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

<section class="listings">

   <h1 class="heading">All Listings</h1>







   <div class="print-button-container">
      <button class="print-button" onclick="window.print()">Print Table</button>
   </div>












   <form action="" method="POST" class="search-form">
      <input type="text" name="search_box" placeholder="Search listings..." maxlength="100" required>
      <button type="submit" class="fas fa-search" name="search_btn"></button>
   </form>

   <div class="box-container">
      <table id="listings">
         <thead>
            <tr>
               <th>#</th>
               <th>Owner</th>
               <th>Property Name</th>
               <th>Price</th>
               <th>Location</th>
               <th>Date Posted</th>
               <th>Action</th>
            </tr>
         </thead>
         <tbody>
            <?php
            $rowNumber = 1;
            if($select_listings->rowCount() > 0){
               while($fetch_listing = $select_listings->fetch(PDO::FETCH_ASSOC)){

                  $listing_id = $fetch_listing['id'];

                  $select_user = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
                  $select_user->execute([$fetch_listing['user_id']]);
                  $fetch_user = $select_user->fetch(PDO::FETCH_ASSOC);

                  echo '<tr>';
                  echo '<td>' . $rowNumber . '</td>';
                  echo '<td>' . $fetch_user['name'] . '</td>';
                  echo '<td>' . $fetch_listing['property_name'] . '</td>';
                  echo '<td>' . $fetch_listing['price'] . '</td>';
                  echo '<td>' . $fetch_listing['address'] . '</td>';
                  echo '<td>' . $fetch_listing['date'] . '</td>';
                  echo '<td>';
                  echo '<form action="" method="POST">';
                  echo '<input type="hidden" name="delete_id" value="' . $listing_id . '">';
                  echo '<button type="submit" name="delete" class="delete-btn">Delete Listing</button>';
                  echo '</form>';
                  echo '</td>';
                  echo '</tr>';

                  $rowNumber++;
               }
            }
            elseif(isset($_POST['search_btn'])){
               echo '<tr><td colspan="7" class="empty">No results found!</td></tr>';
            }
            else{
               echo '<tr><td colspan="7" class="empty">No property posted yet!</td></tr>';
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
