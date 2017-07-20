<html lang="en">

<head>
<title>PHP AngularJS CRUD</title>

	<meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<!-- Angular JS -->
	<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.3.2/angular.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.3.2/angular-route.min.js"></script>

	<!-- MY App -->
	<script src="app/packages/dirPagination.js"></script>
	<script src="app/routes.js"></script>
	<script src="app/services/myServices.js"></script>
	<script src="app/helper/myHelper.js"></script>
	<!-- App Controller -->
	<script src="app/components/home/ItemController.js"></script>
	<script src="app/shared/navbar/navbarDirective.js"></script>

</head>
<body ng-app="main-App">

		
		<ng-view></ng-view>
		

</body>
</html>