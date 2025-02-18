<?php
include 'config.php';
include 'session.php';

// Fetch data from the database
// Properties posted
$sql_properties = "SELECT COUNT(*) AS total_properties FROM properties WHERE admin_id = ?";
$stmt_properties = $conn->prepare($sql_properties);
$stmt_properties->bind_param('i', $_SESSION['admin_id']);
$stmt_properties->execute();
$result_properties = $stmt_properties->get_result();
$total_properties = $result_properties->fetch_assoc()['total_properties'];

// Users who viewed properties
$sql_views = "SELECT COUNT(DISTINCT user_id) AS total_views FROM property_views WHERE property_id IN (SELECT id FROM properties WHERE admin_id = ?)";
$stmt_views = $conn->prepare($sql_views);
$stmt_views->bind_param('i', $_SESSION['admin_id']);
$stmt_views->execute();
$result_views = $stmt_views->get_result();
$total_views = $result_views->fetch_assoc()['total_views'];

// Booked houses
$sql_booked = "SELECT COUNT(*) AS total_booked FROM bookings WHERE property_id IN (SELECT id FROM properties WHERE admin_id = ?)";
$stmt_booked = $conn->prepare($sql_booked);
$stmt_booked->bind_param('i', $_SESSION['admin_id']);
$stmt_booked->execute();
$result_booked = $stmt_booked->get_result();
$total_booked = $result_booked->fetch_assoc()['total_booked'];

// Houses with pending payments
$sql_pending = "SELECT COUNT(*) AS total_pending FROM properties WHERE admin_id = ? AND payment_status = 'pending'";
$stmt_pending = $conn->prepare($sql_pending);
$stmt_pending->bind_param('i', $_SESSION['admin_id']);
$stmt_pending->execute();
$result_pending = $stmt_pending->get_result();
$total_pending = $result_pending->fetch_assoc()['total_pending'];

// Houses fully paid and sold
$sql_sold = "SELECT COUNT(*) AS total_sold FROM properties WHERE admin_id = ? AND payment_status = 'paid' AND sold = 1";
$stmt_sold = $conn->prepare($sql_sold);
$stmt_sold->bind_param('i', $_SESSION['admin_id']);
$stmt_sold->execute();
$result_sold = $stmt_sold->get_result();
$total_sold = $result_sold->fetch_assoc()['total_sold'];

// Handle property upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_property'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $image = $_FILES['image']['name'];
    
    // Move uploaded image to uploads directory
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);

    // Insert property into database
    $sql_insert = "INSERT INTO properties (admin_id, title, description, image, price) VALUES (?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param('isssd', $_SESSION['user_id'], $title, $description, $image, $price);
    $stmt_insert->execute();
    $stmt_insert->close();
    $message = "Property uploaded successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css"> 
    <style>
            body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
            background-image: url('images/img1.jpg');
            background-size: cover;
        }

        .header {
            background-color: #007bff;
            color: white;
            padding: 10px 0;
            background-image: url('images/img1.jpg');
            background-size: cover;
        }

        .header .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .header .navbar-nav .nav-link {
            color: black;
            font-weight: bold;
        }

        .header .navbar-nav .nav-link:hover {
            color: #d3d3d3;
        }

        .container {
            margin-top: 50px;
        }

        .card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .card-header {
            font-size: 1.5rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .card-title {
            font-size: 2.5rem;
            font-weight: bold;
        }

        .card-text {
            font-size: 1.2rem;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            transition: background-color 0.3s, border-color 0.3s;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004a99;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            transition: background-color 0.3s, border-color 0.3s;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }

        h1, h2 {
            color: #343a40;
            font-weight: bold;
        }

        .alert {
            border-radius: 10px;
            font-weight: bold;
        }

        /* Custom styling for radio buttons and pop-up message */
        .radio-btn-container {
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
        }

        .radio-btn-container label {
            font-size: 1.2rem;
            margin-right: 15px;
        }

        .radio-btn-container input[type="radio"] {
            margin-right: 10px;
            transform: scale(1.5);
            accent-color: #007bff;
        }

        .popup-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            visibility: hidden;
            opacity: 0;
            transition: visibility 0s, opacity 0.3s;
        }

        .popup-content {
            background-color: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }

        .popup-content h2 {
            font-size: 1.8rem;
            margin-bottom: 20px;
        }

        .popup-content .btn {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 1rem;
            border-radius: 8px;
        }

        .popup-container.show {
            visibility: visible;
            opacity: 1;
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="navbar navbar-expand-lg navbar-light">
            <a class="navbar-brand" href="#">Admins Dashboard</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Properties</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Bookings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Payments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Logout</a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="mb-4">Admin Dashboard</h1>
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card bg-primary text-white">
                            <a href="view.php"><div class="card-header text-white">Total Properties</div></a>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $total_properties; ?></h5>
                                <p class="card-text">Number of properties you have posted.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card bg-success text-white">
                            <div class="card-header">Total Views</div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $total_views; ?></h5>
                                <p class="card-text">Number of users who have viewed your properties.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card bg-warning text-dark">
                            <div class="card-header">Total Booked</div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $total_booked; ?></h5>
                                <p class="card-text">Number of houses that have been booked.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card bg-danger text-white">
                            <div class="card-header">Pending Payments</div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $total_pending; ?></h5>
                                <p class="card-text">Number of houses with pending payments.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card bg-dark text-white">
                            <div class="card-header">Fully Paid and Sold</div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $total_sold; ?></h5>
                                <p class="card-text">Number of houses that are fully paid for and already sold.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Property Upload Form -->
                <div class="mt-5">
                    <h2>Upload New Property</h2>
                    <?php if (isset($message)) echo "<div class='alert alert-success'>$message</div>"; ?>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="price">Price</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="location">Location</label>
                            <input type="text" class="form-control" id="location" name="location" required>

                        </div>
                        <div class="form-group">
                            <label for="image">Image</label>
                            <input type="file" class="form-control-file" id="image" name="image" required>
                        </div>
                        <button type="button" class="btn btn-primary" id="uploadPropertyButton">Upload Property</button>

                    </form>
                </div>
                <!-- Exit Button -->
                <div class="mt-5">
                    <a href="index.php" class="btn btn-secondary">Exit Dashboard</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
   <!-- Property Type Modal -->
<div class="modal fade" id="propertyTypeModal" tabindex="-1" aria-labelledby="propertyTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="propertyTypeModalLabel">Select Property Type</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="propertyTypeForm">
                    <div class="form-group">
                        <label for="propertyType">What type of property are you uploading?</label>
                        <div>
                            <input type="radio" id="houses" name="propertyType" value="houses" required>
                            <label for="houses">Houses</label>
                        </div>
                        <div id="houseOptions" style="display: none; margin-left: 20px;">
                            <input type="radio" id="for_rent" name="houseOption" value="for_rent" required>
                            <label for="for_rent">For Rent</label><br>
                            <input type="radio" id="for_sale" name="houseOption" value="for_sale" required>
                            <label for="for_sale">For Sale/Purchase</label><br>
                            <input type="radio" id="bedsitters" name="houseOption" value="bedsitters" required>
                            <label for="bedsitters">Bedsitters</label><br>
                            <input type="radio" id="hostel" name="houseOption" value="hostel" required>
                            <label for="hostel">Hostel</label>
                        </div>
                        <div>
                            <input type="radio" id="shop" name="propertyType" value="shop" required>
                            <label for="shop">Shop</label>
                        </div>
                        <div>
                            <input type="radio" id="office" name="propertyType" value="office" required>
                            <label for="office">Office</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="proceedButton">Proceed</button>
            </div>
        </div>
    </div>
</div>

   <script>
    $(document).ready(function() {
    // Show the modal when the Upload Property button is clicked
    $('#uploadPropertyButton').on('click', function() {
        $('#propertyTypeModal').modal('show');
    });

    // Show house options when 'houses' is selected
    $('input[name="propertyType"]').on('change', function() {
        if ($(this).val() === 'houses') {
            $('#houseOptions').slideDown();
        } else {
            $('#houseOptions').slideUp();
        }
    });

    // Handle the Proceed button click
    $('#proceedButton').on('click', function() {
        var propertyType = $('input[name="propertyType"]:checked').val();
        var houseOption = $('input[name="houseOption"]:checked').val();

        if (propertyType) {
            $('#propertyTypeModal').modal('hide');

            // Append the selected property type and house option to the form
            $('<input>').attr({
                type: 'hidden',
                name: 'propertyType',
                value: propertyType
            }).appendTo('#propertyTypeForm');

            if (houseOption) {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'houseOption',
                    value: houseOption
                }).appendTo('#propertyTypeForm');
            }

            // Now submit the form
            $('#propertyTypeForm').submit();
        } else {
            alert('Please select a property type.');
        }
    });
});
</script>
   
</body>
</html>
