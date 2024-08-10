<?php
include 'config.php';
include 'session.php';

// Fetch the total number of properties uploaded by the user
$sql_properties_count = "SELECT property_count FROM users WHERE id = ?";
$stmt_properties_count = $conn->prepare($sql_properties_count);
$stmt_properties_count->bind_param('i', $_SESSION['user_id']);
$stmt_properties_count->execute();
$result_properties_count = $stmt_properties_count->get_result();
$row = $result_properties_count->fetch_assoc();
$total_properties = $row ? $row['property_count'] : 0;

// Fetch all properties uploaded by the user
$sql_properties = "SELECT * FROM properties WHERE admin_id = ?";
$stmt_properties = $conn->prepare($sql_properties);
$stmt_properties->bind_param('i', $_SESSION['user_id']);
$stmt_properties->execute();
$result_properties = $stmt_properties->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Uploaded Properties</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
            background-image: url('images/img1.jpg');
            background-size: cover;
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
            background-color: #007bff;
            color: white;
        }

        .card-body {
            text-align: center;
        }

        .card-title {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .card-text {
            font-size: 1.2rem;
            margin-bottom: 20px;
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

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            background-color: white;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 15px;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Your Uploaded Properties</div>
                    <div class="card-body">
                        <h5 class="card-title">Total Properties Uploaded: <?php echo $total_properties; ?></h5>
                        <table>
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Image</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($property = $result_properties->fetch_assoc()) { ?>
                                    <tr>
                                        <td><?php echo $property['title']; ?></td>
                                        <td><?php echo $property['description']; ?></td>
                                        <td><?php echo $property['price']; ?></td>
                                        <td><img src="uploads/<?php echo $property['image']; ?>" alt="<?php echo $property['title']; ?>" width="100"></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        <a href="my-properties.php" class="btn btn-primary mt-3">Upload More Properties</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
