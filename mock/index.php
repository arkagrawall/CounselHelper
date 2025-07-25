<?php
include 'config.php';

$servername = $DB_HOST;
$username   = $DB_USER;
$password   = $DB_PASS;
$dbname     = $DB_NAME;


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch colleges and branches
$sql = "SELECT DISTINCT TRIM(SUBSTRING_INDEX(Institute, ',', 1)) AS College, Branch FROM aktu";
$result = $conn->query($sql);

$colleges = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $colleges[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mock AKTU Choice Filler</title>
  <link rel="icon" href="assets/images/logo.png" type="image/x-icon">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.0/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.16/jspdf.plugin.autotable.min.js"></script>
  <style>
    .container {
      max-width: 100%;
      padding: 1rem;
    }
    .table-container {
      max-height: 75vh;
      overflow-y: auto;
      border: 1px solid #ccc;
      border-radius: 0.5rem;
    }
    .table {
      width: 100%;
      border-collapse: collapse;
    }
    .table th, .table td {
      border: 1px solid #ddd;
      padding: 8px;
      text-align: left;
      word-wrap: break-word; /* Ensures text wraps within cells */
    }
    .table th {
      background-color: #f2f2f2;
    }
    .dark-mode {
      background-color: #121212;
      color: #e0e0e0;
    }
    .dark-mode .table th {
      background-color: #444;
      color: #fff;
    }
    .dark-mode .table td {
      background-color: #555;
      color: #fff;
    }
    .dark-mode .bg-gray-50 {
      background-color: #1e1e1e;
    }
    .dark-mode .bg-blue-800 {
      background-color: #1a202c;
    }
    .dark-mode .bg-gray-700 {
      background-color: #2d3748;
    }
    .dark-mode .bg-green-500 {
      background-color: #48bb78;
    }
    .dark-mode .bg-red-500 {
      background-color: #f56565;
    }
    .dark-mode .bg-blue-600 {
      background-color: #2b6cb0;
    }
    .dark-mode .bg-blue-700 {
      background-color: #2c5282;
    }
    .dark-mode .bg-yellow-200 {
      background-color: #f6e05e;
    }
    .flex-grow-0 {
      flex-grow: 0;
    }
    .input-box {
      position: relative;
      margin-bottom: 1rem;
    }
    .input-box input {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid #ddd;
      border-radius: 0.5rem;
      font-size: 1rem;
      transition: border-color 0.3s ease;
    }
    .input-box input:focus {
      border-color: #1e3a8a;
      outline: none;
    }
    .input-box label {
      position: absolute;
      top: -0.75rem;
      left: 0.75rem;
      background-color: white;
      padding: 0 0.25rem;
      font-size: 0.875rem;
      color: #1e3a8a;
      transition: all 0.3s ease;
    }
    .input-box input:focus + label,
    .input-box input:not(:placeholder-shown) + label {
      top: -1.5rem;
      font-size: 0.75rem;
      color: #1e3a8a;
    }
  </style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">

<header class="bg-blue-800 text-white p-4 shadow-lg flex justify-between items-center">
  <div class="flex items-center">
    <img src="assets/images/logo.png" alt="Logo" class="h-8 mr-2">
    <h1 class="text-3xl font-bold">Mock AKTU Choice Filler</h1>
  </div>
  <div class="flex items-center space-x-4">
    <button id="darkModeToggle" class="bg-gray-700 text-white px-3 py-1 rounded-full"><i class="fas fa-moon"></i></button>
    <a href="https://aktu.tech" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full focus:outline-none focus:shadow-outline transition duration-150 ease-in-out transform hover:scale-105">Home</a>
    <a href="https://www.linkedin.com/in/arkagrawal/" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full focus:outline-none focus:shadow-outline transition duration-150 ease-in-out transform hover:scale-105">Join Me</a>
  </div>
</header>

<div id="disclaimer" class="container mx-auto p-4 flex-grow-0">
  <div class="bg-yellow-200 p-4 rounded-lg shadow-lg">
    <p class="text-center text-lg font-bold">Disclaimer: This is a mock choice filling system for AKTU. The choices you make here will not affect your actual seat allotment. Use this tool for practice only. Please enter your real name and rank using mock names can lead to ban and will not save your data.</p>
    <div class="text-center mt-4">
      <button id="proceedButton" class="bg-blue-500 text-white font-bold py-2 px-4 rounded">Proceed</button>
    </div>
  </div>
</div>

<div id="formPopup" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center hidden">
  <div class="bg-white p-8 rounded-lg shadow-lg">
    <h2 class="text-2xl font-bold mb-4">Enter Your Details</h2>
    <form id="userForm">
      <div class="input-box">
        <input type="text" id="name" name="name" required placeholder=" ">
        <label for="name">Name</label>
      </div>
      <div class="input-box">
        <input type="number" id="rank" name="rank" required placeholder=" ">
        <label for="rank">Rank</label>
      </div>
      <div class="text-center">
        <button type="submit" class="bg-blue-500 text-white font-bold py-2 px-4 rounded">Submit</button>
      </div>
    </form>
    <p class="text-sm text-gray-700 mt-4 text-center">You need to enter the same name and rank combination to view your filled choices next time. Using incorrect details can lead to data loss.</p>
    <div class="text-center mt-4">
      <a href="https://www.linkedin.com/in/arkagrawal/" target="_blank" class="bg-blue-500 text-white font-bold py-2 px-4 rounded">Contact @arkagrawal</a>
    </div>
  </div>
</div>

<div id="choiceFiller" class="container mx-auto p-8 flex-grow hidden">
  <div class="flex justify-between mb-4">
    <div class="flex space-x-4">
      <button class="bg-green-500 text-white font-bold py-2 px-4 rounded-full">Unfilled Choices: <span id="unfilledCount"><?php echo count($colleges); ?></span></button>
      <button class="bg-blue-500 text-white font-bold py-2 px-4 rounded-full">Filled Choices: <span id="filledCount">0</span></button>
    </div>
    <div>
      <button id="downloadPdf" class="bg-red-500 text-white font-bold py-2 px-4 rounded-full">Save as PDF</button>
    </div>
    <div class="flex space-x-4">
      <div class="bg-blue-500 text-white font-bold py-2 px-4 rounded-full">Name: <span id="userName"></span></div>
      <div class="bg-blue-500 text-white font-bold py-2 px-4 rounded-full">Rank: <span id="userRank"></span></div>
    </div>
  </div>
  <div class="flex justify-between">
    <div class="w-1/2 pr-4">
      <h2 class="text-2xl font-bold mb-4">Available Colleges and Branches</h2>
      <input type="text" id="leftSearch" placeholder="Search..." class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">
      <div class="table-container">
        <table class="table" id="availableTable">
          <thead>
            <tr>
              <th>College</th>
              <th>Branch</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($colleges as $college): ?>
              <tr>
                <td><?php echo htmlspecialchars($college['College']); ?></td>
                <td><?php echo htmlspecialchars($college['Branch']); ?></td>
                <td><button class="add-button bg-green-500 text-white px-2 py-1 rounded">Add</button></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <div class="w-1/2 pl-4">
      <h2 class="text-2xl font-bold mb-4">Your Choices</h2>
      <input type="text" id="rightSearch" placeholder="Search..." class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">
      <div class="table-container">
        <table class="table" id="choicesTable">
          <thead>
            <tr>
              <th>#</th>
              <th>College</th>
              <th>Branch</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Mobile message -->
<div class="container mx-auto p-8 flex-grow block md:hidden">
  <div class="bg-yellow-200 p-4 rounded-lg shadow-lg">
    <p class="text-center text-lg font-bold">Please use a PC or laptop for choice filling. Mobile is not compatible.</p>
  </div>
</div>

<!-- Footer -->
<footer class="bg-blue-800 text-white p-4 mt-8 shadow-lg flex-shrink-0">
  <div class="container mx-auto flex flex-col md:flex-row justify-between items-center text-center md:text-left">
    <p class="mb-4 md:mb-0">&copy; 2024 AKTU Choice Filling Helper. All Rights Reserved.</p>
    <div class="flex space-x-4">
      <a href="https://www.linkedin.com/in/arkagrawal/" target="_blank" class="hover:text-blue-400 transition duration-150 ease-in-out transform hover:scale-105">Join Me</a>
      <a href="https://www.linkedin.com/in/arkagrawal/" target="_blank" class="hover:text-blue-400 transition duration-150 ease-in-out transform hover:scale-105">Made & Managed by @arkagrawal</a>
    </div>
  </div>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const leftSearchInput = document.getElementById('leftSearch');
  const rightSearchInput = document.getElementById('rightSearch');
  const availableTable = document.getElementById('availableTable').getElementsByTagName('tbody')[0];
  const choicesTable = document.getElementById('choicesTable').getElementsByTagName('tbody')[0];
  const unfilledCount = document.getElementById('unfilledCount');
  const filledCount = document.getElementById('filledCount');
  const darkModeToggle = document.getElementById('darkModeToggle');
  const proceedButton = document.getElementById('proceedButton');
  const formPopup = document.getElementById('formPopup');
  const userForm = document.getElementById('userForm');
  const choiceFiller = document.getElementById('choiceFiller');
  const userNameDisplay = document.getElementById('userName');
  const userRankDisplay = document.getElementById('userRank');
  const downloadPdfButton = document.getElementById('downloadPdf');
  let darkMode = false;
  let userName = '';
  let userRank = '';

  darkModeToggle.addEventListener('click', function() {
    darkMode = !darkMode;
    document.body.classList.toggle('dark-mode', darkMode);
    darkModeToggle.innerHTML = darkMode ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
  });

  proceedButton.addEventListener('click', function() {
    document.getElementById('disclaimer').style.display = 'none';
    formPopup.classList.remove('hidden');
  });

  userForm.addEventListener('submit', function(event) {
    event.preventDefault();
    userName = document.getElementById('name').value;
    userRank = document.getElementById('rank').value;
    userNameDisplay.textContent = userName;
    userRankDisplay.textContent = userRank;

    // Check user existence
    fetch(`check_user.php?name=${encodeURIComponent(userName)}&rank=${encodeURIComponent(userRank)}`)
      .then(response => response.json())
      .then(data => {
        if (data.exists) {
          // User exists, load saved choices
          loadSavedChoices(userName, userRank);
        } else {
          // Register new user
          registerNewUser(userName, userRank);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error checking user existence');
      });
  });

  function loadSavedChoices(name, rank) {
    fetch(`get_choices.php?name=${encodeURIComponent(name)}&rank=${encodeURIComponent(rank)}`)
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          choiceFiller.classList.remove('hidden');
          formPopup.classList.add('hidden');
          data.choices.forEach(choice => {
            const newRow = document.createElement('tr');
            newRow.innerHTML = `<td></td>
                                <td>${choice.college}</td>
                                <td>${choice.branch}</td>
                                <td>
                                  <button class="up-button bg-blue-500 text-white px-2 py-1 rounded">Up</button>
                                  <button class="down-button bg-blue-500 text-white px-2 py-1 rounded">Down</button>
                                  <button class="remove-button bg-red-500 text-white px-2 py-1 rounded">Remove</button>
                                </td>`;
            choicesTable.appendChild(newRow);
          });
          updateCounts();
          updateChoiceNumbers();
        } else {
          alert('Error loading saved choices');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error loading saved choices');
      });
  }

  function registerNewUser(name, rank) {
    fetch('register_user.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ name: name, rank: rank })
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          choiceFiller.classList.remove('hidden');
          formPopup.classList.add('hidden');
        } else {
          alert('User is already registered');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error registering user');
      });
  }

  leftSearchInput.addEventListener('keyup', function() {
    const filter = leftSearchInput.value.toLowerCase();
    const rows = availableTable.getElementsByTagName('tr');
    Array.from(rows).forEach(row => {
      const college = row.getElementsByTagName('td')[0].textContent.toLowerCase();
      const branch = row.getElementsByTagName('td')[1].textContent.toLowerCase();
      if (college.includes(filter) || branch.includes(filter)) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });
  });

  rightSearchInput.addEventListener('keyup', function() {
    const filter = rightSearchInput.value.toLowerCase();
    const rows = choicesTable.getElementsByTagName('tr');
    Array.from(rows).forEach(row => {
      const college = row.getElementsByTagName('td')[1].textContent.toLowerCase();
      const branch = row.getElementsByTagName('td')[2].textContent.toLowerCase();
      if (college.includes(filter) || branch.includes(filter)) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });
  });

  document.addEventListener('click', function(event) {
    if (event.target.classList.contains('add-button')) {
      const row = event.target.closest('tr');
      const college = row.getElementsByTagName('td')[0].textContent;
      const branch = row.getElementsByTagName('td')[1].textContent;

      const newRow = document.createElement('tr');
      newRow.innerHTML = `<td></td>
                          <td>${college}</td>
                          <td>${branch}</td>
                          <td>
                            <button class="up-button bg-blue-500 text-white px-2 py-1 rounded">Up</button>
                            <button class="down-button bg-blue-500 text-white px-2 py-1 rounded">Down</button>
                            <button class="remove-button bg-red-500 text-white px-2 py-1 rounded">Remove</button>
                          </td>`;
      choicesTable.appendChild(newRow);
      row.remove();
      updateCounts();
      updateChoiceNumbers();
      saveChoices();
    }

    if (event.target.classList.contains('remove-button')) {
      const row = event.target.closest('tr');
      const college = row.getElementsByTagName('td')[1].textContent;
      const branch = row.getElementsByTagName('td')[2].textContent;

      const newRow = document.createElement('tr');
      newRow.innerHTML = `<td>${college}</td>
                          <td>${branch}</td>
                          <td><button class="add-button bg-green-500 text-white px-2 py-1 rounded">Add</button></td>`;
      availableTable.appendChild(newRow);
      row.remove();
      updateCounts();
      updateChoiceNumbers();
      saveChoices();
    }

    if (event.target.classList.contains('up-button')) {
      const row = event.target.closest('tr');
      if (row.previousElementSibling) {
        choicesTable.insertBefore(row, row.previousElementSibling);
      }
      updateChoiceNumbers();
      saveChoices();
    }

    if (event.target.classList.contains('down-button')) {
      const row = event.target.closest('tr');
      if (row.nextElementSibling) {
        choicesTable.insertBefore(row.nextElementSibling, row);
      }
      updateChoiceNumbers();
      saveChoices();
    }
  });

  function updateCounts() {
    unfilledCount.textContent = availableTable.getElementsByTagName('tr').length;
    filledCount.textContent = choicesTable.getElementsByTagName('tr').length;
  }

  function updateChoiceNumbers() {
    const rows = choicesTable.getElementsByTagName('tr');
    Array.from(rows).forEach((row, index) => {
      row.getElementsByTagName('td')[0].textContent = index + 1;
    });
  }

  function saveChoices() {
    const choices = [];
    const rows = choicesTable.getElementsByTagName('tr');
    Array.from(rows).forEach(row => {
      const college = row.getElementsByTagName('td')[1].textContent;
      const branch = row.getElementsByTagName('td')[2].textContent;
      choices.push({ college: college, branch: branch });
    });

    fetch('save_choices.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ name: userName, rank: userRank, choices: choices })
    })
      .then(response => response.json())
      .then(data => {
        if (!data.success) {
          alert('Error saving choices');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error saving choices');
      });
  }

  downloadPdfButton.addEventListener('click', function() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    // Add the background image first
    const background = new Image();
    background.src = 'Mock by.png';
    background.onload = function() {
      for (let i = 1; i <= doc.internal.getNumberOfPages(); i++) {
        doc.setPage(i);
        doc.addImage(background, 'PNG', 0, 0, doc.internal.pageSize.getWidth(), doc.internal.pageSize.getHeight());
      }

      doc.setFontSize(20);
      doc.text('Mock AKTU Choice Filler', 105, 20, { align: 'center' });
      doc.addImage('assets/images/logo.png', 'PNG', 90, 25, 30, 30);

      const userName = userNameDisplay.textContent;
      const userRank = userRankDisplay.textContent;

      doc.setFontSize(14);
      doc.setFont("helvetica", "bold");
      doc.text(`Name: ${userName}`, 10, 70);
      doc.text(`Rank: ${userRank}`, 10, 80);
      doc.setFontSize(12);
      doc.setFont("helvetica", "normal");
      doc.text("Join: https://www.linkedin.com/in/arkagrawal/", 10, 90);
      doc.text("Visit Website: https://aktu.tech", 10, 100);

      const data = [];
      const rows = choicesTable.getElementsByTagName('tr');
      Array.from(rows).forEach(row => {
        const choiceNumber = row.getElementsByTagName('td')[0].textContent;
        const college = row.getElementsByTagName('td')[1].textContent;
        const branch = row.getElementsByTagName('td')[2].textContent;
        data.push([choiceNumber, college, branch]);
      });

      doc.autoTable({
        head: [['#', 'College', 'Branch']],
        body: data,
        startY: 110,
        theme: 'grid',
        styles: { overflow: 'linebreak' }
      });
      
      doc.save('Mock_AKTU_Choice_Filler.pdf');
    };
  });
});
</script>

</body>
</html>
