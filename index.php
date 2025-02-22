<?php

include './condb.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $custID = trim($_POST['custID']);

  // ตรวจสอบว่ากรอกข้อมูลครบหรือไม่
  if (empty($custID)) {
    $error = "Please enter your UserID.";
  } else if ($custID === 'admin') {
    header("Location: ./dashboard/adminDashboard.php");
    exit();
  } else if (!preg_match('/^C00[1-9]$/', $custID)) {
    $error = "Invalid UserID format. Please use C001 to C009.";
  } else {
    // ค้นหาข้อมูลผู้ใช้ใน Database
    $stmt = $conn->prepare("SELECT IDCust, CustName, Tel, Address FROM customer WHERE IDCust = ?");
    $stmt->bind_param("s", $custID);
    $stmt->execute();
    $result = $stmt->get_result();

    // ตรวจสอบว่าพบข้อมูลหรือไม่
    if ($row = $result->fetch_assoc()) {
      // สร้าง array สำหรับ selected_customers[]
      $selected_customers_array = array($row['IDCust']);

      // สร้าง array แบบ associative สำหรับ customer_names[IDCust]
      $customer_names_array = array($row['IDCust'] => $row['CustName']);

      // สร้าง query string โดยใช้ http_build_query()
      $query_params = http_build_query(array(
        'selected_customers' => $selected_customers_array,
        'customer_names' => $customer_names_array,
      ));

      // สร้าง URL ใหม่โดยรวม base URL กับ query string ที่สร้างขึ้น
      header("Location: ./catalog.php?" . $query_params);
      exit(); // หยุดการทำงานหลังจาก redirect
    } else {
      $error = "Don't have this UserID in database";
    }
    $stmt->close();
  }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <title>Log In</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      /* Fallback background color */
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      position: relative;
      /* For pseudo-element positioning */
    }

    body::before {
      content: "";
      background-image: url("https://www.healthyeating.org/images/default-source/home-0.0/nutrition-topics-2.0/general-nutrition-wellness/2-2-2-3foodgroups_fruits_detailfeature.jpg?sfvrsn=64942d53_4");
      background-repeat: no-repeat;
      background-size: cover;
      position: absolute;
      top: 0;
      right: 0;
      bottom: 0;
      left: 0;
      z-index: -2;
      /* Place background image further back */
    }

    body::after {
      content: "";
      background-color: grey;
      /* Grey color layer */
      position: absolute;
      top: 0;
      right: 0;
      bottom: 0;
      left: 0;
      opacity: 0.4;
      /* Transparency for the grey layer */
      z-index: -1;
      /* Place grey layer in front of background image, but behind content */
    }

    .login-box {
      background-color: white;
      width: 350px;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      text-align: center;
      position: relative;
      z-index: 1;
      /* Ensure login-box is on top */
    }

    .login-box h2 {
      margin-bottom: 20px;
      color: #333;
    }

    .error {
      color: #721c24;
      background-color: #f8d7da;
      border: 1px solid #f5c6cb;
      border-radius: 5px;
      padding: 10px;
      margin-bottom: 20px;
    }

    input[type="text"] {
      width: 100%;
      padding: 12px;
      margin-bottom: 20px;
      border: 1px solid #ccc;
      border-radius: 5px;
      box-sizing: border-box;
      font-size: 16px;
    }

    button[type="submit"] {
      background-color: #5cb85c;
      color: white;
      padding: 12px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
    }

    button[type="submit"]:hover {
      background-color: #4cae4c;
    }
  </style>
</head>

<body>
  <div class="login-box">
    <h2>Log In</h2>
    <?php if (!empty($error))
      echo "<p class='error'>$error</p>"; ?>
    <form method="POST">
      <input type="text" name="custID" placeholder="UserID" required>
      <button type="submit">Log in</button>
    </form>
  </div>
</body>

</html>