<!DOCTYPE html>
<?php 
	$servername = "localhost";
	$username = "YOUR_USERNAME_HERE";
	$password = "YOUR_PASSWORD_HERE";
	$dbname = "YOUR_DATABASE_NAME_HERE";
	$conn = new mysqli($servername, $username, $password, $dbname);
	if ($conn->connect_error) {    
		die("Connection failed: " . $conn->connect_error);	
	}else{	
	
	}
	$sql1 = "SELECT * FROM `paymentlinks` WHERE `token` = '".$_GET['expire']."'";
	if ($res1 = mysqli_query($conn, $sql1)) {	
		if (mysqli_num_rows($res1) > 0) {		
			while ($row1 = mysqli_fetch_array($res1)) {           
				$expire = $row1['expire'];       
			}	
		}
	}

	$date_now = strtotime("now");
	$date2    = $expire;
	if ($date_now > $date2) {
		echo '<div style="
		background: #e7e7e7;
		padding: 20px;
		text-align: center;
		font-family: Arial;
		width: 80%;
		margin: 0 auto;
		"><h2>Sorry Payment Link is expired, Please try again</h2></div>';
		exit;
	}

	$sql = "SELECT b.*,p.`property_name` FROM `bookings` as b INNER JOIN `properties` as p ON b.`property_id` = p.`id` WHERE b.`id` = '".$_GET['id']."'";
	if ($res = mysqli_query($conn, $sql)) {	
		if (mysqli_num_rows($res) > 0) {		
			while ($row = mysqli_fetch_array($res)) {           
				$guest = $row['guest'];		   
				$price = $row['price'];		   
				$property_name = $row['property_name'];		   
				$booking_no = $row['booking_no'];		   
				$checkin = $row['checkin'];		   
				$checkout = $row['checkout'];        
			}	
		}
	}
	
	if(isset($_GET['partial']) && $_GET['partial'] == 'yes'){
		$percentage = 60;
		$price = ($percentage / 100) * $price;
	}
	
	$account = $_GET['account'];
	$pitype = $_GET['pitype'];
	
	if($_GET['pitype'] == 'test'){
		$pikey = 'rzp_test_KEY';
	}else{
		$pikey = 'rzp_live_KEY';
	}
?>
<html>
<head>
  <title>Pronto - Payment</title>
</head>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script> 
<style>
  .card-product .img-wrap {
    border-radius: 3px 3px 0 0;
    overflow: hidden;
    position: relative;
    height: 220px;
    text-align: center;
  }
  .card-product .img-wrap img {
    max-height: 100%;
    max-width: 100%;
    object-fit: cover;
  }
  .card-product .info-wrap {
    overflow: hidden;
    padding: 15px;
    border-top: 1px solid #eee;
  }
  .card-product .bottom-wrap {
    padding: 15px;
    border-top: 1px solid #eee;
  }
  .label-rating { margin-right:10px;
    color: #333;
    display: inline-block;
    vertical-align: middle;
  }
  .card-product .price-old {
    color: #999;
  }
</style>
<body>
<div class="container">
<br><br><br>

</div> 
<!--container.//-->

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>

  //$('body').on('click', '.buy_now', function(e){
  $( document ).ready(function(e) {
    var totalAmount = <?php echo $price; ?>;
    var product_id =  <?php echo $_GET['id']; ?>;	
	var villaname =  '<?php echo $property_name; ?>';	
	var guest =  '<?php echo $guest; ?>';	
	var booking_no =  '<?php echo $booking_no; ?>';	
	var checkin =  '<?php echo $checkin; ?>';	
	var checkout =  '<?php echo $checkout; ?>';
    var options = {
    "key": "<?php echo $pikey; ?>",
    "amount": (<?php echo $price; ?>*100), // 2000 paise = INR 20
    "name": "<?php echo $property_name; ?>",
    "description": "Payment",
    "image": "https://prontoinfosys.com/wp-content/uploads/2020/06/5-svg-1.png",
    "handler": function (response){
        //alert(response.razorpay_payment_id);
          $.ajax({
            url: 'https://domain-url/payment-process.php',
            type: 'post',
            dataType: 'json',
            data: {
                razorpay_payment_id: response.razorpay_payment_id , 				
				villaname:villaname, 				
				totalAmount : totalAmount,				
				product_id : product_id,				
				villaname : villaname,				
				guest : guest,				
				booking_no : booking_no,				
				checkin : checkin,				
				checkout : checkout,
            }, 
            success: function (msg) {
                alert(msg.status);
				if(msg.status == true){
					window.location.href = 'https://domain-url/success.php';
				}else{
					window.location.href = 'https://domain-url/failure.php?id='+product_id;
				}
            }
        });
     
    },
    "modal": {
      "ondismiss": function () {
        if (confirm("Are you sure, you want to close the form?")) {
          txt = "You pressed OK!";
		  window.location.href = 'https://domain-url/failure.php?id='+product_id;
          console.log("Checkout form closed by the user");
        } else {
          txt = "You pressed Cancel!";
          console.log("Complete the Payment")
        }
      }
    },

    "theme": {
        "color": "#42276c"
    }
  };
  var rzp1 = new Razorpay(options);
  rzp1.open();
  e.preventDefault();
  });

</script>
</body>
</html>