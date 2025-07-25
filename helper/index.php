<?php
include 'config.php';
$results = [];
$spot_chances = [];
$error = '';

$rank = '';
$quota = '';
$seat = '';
$location = [];
$selected_round = 'all'; // Default to show all rounds

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rank = $_POST['rank'];
    $quota = $_POST['quota'];
    $seat = $_POST['seat'];
    $location = isset($_POST['location']) ? $_POST['location'] : [];
    $selected_round = $_POST['round']; // Get selected round from POST data

    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // SQL query with dynamic parts based on whether locations are selected
    $sql = "SELECT * FROM aktu WHERE Close >= ? AND Quota = ? AND Seat = ?";
    if (!empty($location)) {
        $location_placeholders = implode(',', array_fill(0, count($location), '?'));
        $sql .= " AND SUBSTRING_INDEX(Institute, ',', -1) IN ($location_placeholders)";
    }
    $sql .= " ORDER BY Round ASC, Close ASC";

    $stmt = $conn->prepare($sql);
    if (!empty($location)) {
        $types = "iss" . str_repeat('s', count($location));
        $params = array_merge([$rank, $quota, $seat], $location);
    } else {
        $types = "iss";
        $params = [$rank, $quota, $seat];
    }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if (abs($row['Close'] - $rank) <= 10000) {  // Adjust the range as needed
                $spot_chances[] = $row;
            }
            $results[] = $row;
        }
    } else {
        $error = "No results found.";
    }

    $stmt->close();
    $conn->close();
}

function getDistinctLocations() {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "SELECT DISTINCT TRIM(SUBSTRING_INDEX(Institute, ',', -1)) AS Location FROM aktu";
    $result = $conn->query($sql);
    $locations = [];
    while ($row = $result->fetch_assoc()) {
        $locations[] = $row['Location'];
    }
    $conn->close();
    return $locations;
}

function sortResults($results, $priorityList) {
    usort($results, function ($a, $b) use ($priorityList) {
        $indexA = array_search($a['Institute'], $priorityList);
        $indexB = array_search($b['Institute'], $priorityList);
        
        if ($indexA === false && $indexB === false) {
            return 0;
        } elseif ($indexA === false) {
            return 1;
        } elseif ($indexB === false) {
            return -1;
        } else {
            return $indexA <=> $indexB;
        }
    });
    return $results;
}

$priorityList = [
    "Institute of Engineering & Technology (IET), Lucknow",
    "Kamla Nehru Institute of Technology (KNIT), Sultanpur",
    "Bundelkhand Institute of Engineering & Technology (BIET), Jhansi",
    "Dr. Ambedkar Institute of Technology for Handicapped, Kanpur",
    "J.S.S. Academy of Technical Education, Noida",
    "Ajay Kumar Garg Engineering College, Ghaziabad",
    "KIET Group of Institutions (Krishna Institute of Engineering & Technology), Ghaziabad",
    "ABES Institute of Technology, Ghaziabad",
    "ABES Engineering College, Ghaziabad",
    "G.L. Bajaj Institute of Technology & Management, Greater Noida",
    "Galgotias College of Engineering & Technology, Greater Noida",
    "Noida Institute of Engineering & Technology (NIET), Greater Noida",
    "PSIT-Pranveer Singh Institute of Technology, Kanpur",
    "IMS Engineering College, Ghaziabad",
    "Raj Kumar Goel Institute of Technology, Ghaziabad",
    "NITRA Technical Campus, Ghaziabad",
    "Greater Noida Institute of Technology (GNIOT), Greater Noida",
    "Meerut Institute of Technology, Meerut",
    "Institute of Engineering & Rural Technology, Allahabad",
    "Babu Banarasi Das Institute of Technology and Management, Lucknow",
    "I.T.S. Engineering College, Greater Noida",
    "Ch. Charan Singh University Campus (SCRIET) Meerut",
    "Uttar Pradesh Textile Technology Institute, Kanpur",
    "Raja Balwant Singh Engineering Technical Campus, Agra",
    "Rajkiya Engineering College, Mainpuri",
    "Rajkiya Engineering College, Ambedkar Nagar",
    "Rajkiya Engineering College, Bijnor",
    "Rajkiya Engineering College, Azamgarh",
    "D.D.U. Gorakhpur University, Gorakhpur",
    "Rajkiya Engineering College, Sonebhadra",
    "Rajkiya Engineering College, Banda",
    "Rajkiya Engineering College, Kannauj",
    "Indian Institute of Handloom Technology, Varanasi"
];

$results = sortResults($results, $priorityList);
$spot_chances = sortResults($spot_chances, $priorityList);

function groupResultsByRound($results) {
    $groupedResults = [];
    foreach ($results as $result) {
        $groupedResults[$result['Round']][] = $result;
    }
    return $groupedResults;
}

$groupedResults = groupResultsByRound($results);
$groupedSpotChances = groupResultsByRound($spot_chances);

$availableRounds = array_keys($groupedResults);
$locations = getDistinctLocations();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AKTU Choice Filling</title>
  <link rel="icon" type="image/png" href="assets/images/logo.png">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
  <script src="js/script.js" defer></script>
  <style>
    .container {
      max-width: 100%;
      padding: 1rem;
    }
    .card {
      background: white;
      padding: 1.5rem;
      border-radius: 0.5rem;
      box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
      margin-bottom: 2rem;
    }
    .highlight-card {
      background: #f0fdf4;
      border: 2px solid #10b981;
    }
    .animated-dropdown {
      position: relative;
      display: inline-block;
    }
    .animated-dropdown-content {
      display: none;
      position: absolute;
      background-color: #f9f9f9;
      min-width: 160px;
      box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
      z-index: 1;
    }
    .animated-dropdown:hover .animated-dropdown-content {
      display: block;
      animation: fadeIn 0.5s;
    }
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
  </style>
</head>
<body class="bg-gray-50">

<header class="bg-blue-800 text-white p-4 shadow-lg">
  <div class="container mx-auto flex flex-col md:flex-row justify-between items-center">
    <div class="flex items-center mb-4 md:mb-0">
      <img src="assets/images/logo.png" alt="Logo" class="h-8 mr-2">
      <h1 class="text-3xl font-bold leading-tight md:leading-none">AKTU Choice Filling Helper</h1>
    </div>
    <div class="flex items-center space-x-4">
    <a href="https://aktu.tech" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full focus:outline-none focus:shadow-outline transition duration-150 ease-in-out transform hover:scale-105">Home</a>
    <a href="https://www.linkedin.com/in/arkagrawal/" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full focus:outline-none focus:shadow-outline transition duration-150 ease-in-out transform hover:scale-105">Join Me</a>
  </div>
</header>
  </div>
</header>

<!-- Additional styles to ensure the header title fits in one line on mobile devices -->
<style>
  @media (max-width: 768px) {
    h1.text-3xl {
      font-size: 1.25rem; /* Adjust font size to ensure the title fits in one line */
    }
  }
</style>

<div class="container mx-auto p-8">
  <div class="card">
    <h2 class="text-2xl font-bold mb-6 text-center text-blue-800">Fill Your Preferences</h2>
    <form id="choiceForm" method="POST" action="">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="rank">Rank</label>
          <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:ring-2 focus:ring-blue-500" id="rank" name="rank" type="number" placeholder="Enter your rank" value="<?php echo htmlspecialchars($rank); ?>" required>
        </div>
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="quota">Quota</label>
          <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:ring-2 focus:ring-blue-500" id="quota" name="quota" required>
            <option value="Home State" <?php echo ($quota === 'Home State') ? 'selected' : ''; ?>>Home State</option>
            <option value="All India" <?php echo ($quota === 'All India') ? 'selected' : ''; ?>>All India</option>
          </select>
        </div>
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="seat">Seat Type</label>
          <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:ring-2 focus:ring-blue-500" id="seat" name="seat" required>
            <option value="EWS(GL)" <?php echo ($seat === 'EWS(GL)') ? 'selected' : ''; ?>>EWS(GL)</option>
            <option value="EWS(OPEN)" <?php echo ($seat === 'EWS(OPEN)') ? 'selected' : ''; ?>>EWS(OPEN)</option>
            <option value="OPEN(TF)" <?php echo ($seat === 'OPEN(TF)') ? 'selected' : ''; ?>>OPEN(TF)</option>
            <option value="BC(Girl)" <?php echo ($seat === 'BC(Girl)') ? 'selected' : ''; ?>>BC(Girl)</option>
            <option value="OPEN" <?php echo ($seat === 'OPEN') ? 'selected' : ''; ?>>OPEN</option>
            <option value="OPEN(AF)" <?php echo ($seat === 'OPEN(AF)') ? 'selected' : ''; ?>>OPEN(AF)</option>
            <option value="BC" <?php echo ($seat === 'BC') ? 'selected' : ''; ?>>BC</option>
            <option value="OPEN(GIRL)" <?php echo ($seat === 'OPEN(GIRL)') ? 'selected' : ''; ?>>OPEN(GIRL)</option>
            <option value="BC(AF)" <?php echo ($seat === 'BC(AF)') ? 'selected' : ''; ?>>BC(AF)</option>
            <option value="SC" <?php echo ($seat === 'SC') ? 'selected' : ''; ?>>SC</option>
            <option value="EWS(AF)" <?php echo ($seat === 'EWS(AF)') ? 'selected' : ''; ?>>EWS(AF)</option>
            <option value="SC(Girl)" <?php echo ($seat === 'SC(Girl)') ? 'selected' : ''; ?>>SC(Girl)</option>
            <option value="OPEN(FF)" <?php echo ($seat === 'OPEN(FF)') ? 'selected' : ''; ?>>OPEN(FF)</option>
            <option value="ST" <?php echo ($seat === 'ST') ? 'selected' : ''; ?>>ST</option>
            <option value="OPEN(PH)" <?php echo ($seat === 'OPEN(PH)') ? 'selected' : ''; ?>>OPEN(PH)</option>
            <option value="BC(PH)" <?php echo ($seat === 'BC(PH)') ? 'selected' : ''; ?>>BC(PH)</option>
            <option value="SC(AF)" <?php echo ($seat === 'SC(AF)') ? 'selected' : ''; ?>>SC(AF)</option>
            <option value="ST(Girl)" <?php echo ($seat === 'ST(Girl)') ? 'selected' : ''; ?>>ST(Girl)</option>
            <option value="SC(PH)" <?php echo ($seat === 'SC(PH)') ? 'selected' : ''; ?>>SC(PH)</option>
            <option value="EWS(PH)" <?php echo ($seat === 'EWS(PH)') ? 'selected' : ''; ?>>EWS(PH)</option>
            <option value="BC(FF)" <?php echo ($seat === 'BC(FF)') ? 'selected' : ''; ?>>BC(FF)</option>
          </select>
        </div>
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="location">Preferred Location (Optional)</label>
          <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:ring-2 focus:ring-blue-500" id="location" name="location[]" multiple>
            <?php foreach ($locations as $loc): ?>
              <option value="<?php echo htmlspecialchars($loc); ?>" <?php echo in_array($loc, $location) ? 'selected' : ''; ?>><?php echo htmlspecialchars($loc); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="round">Select Round</label>
          <div class="relative">
            <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:ring-2 focus:ring-blue-500" id="round" name="round" required>
              <option value="all" <?php echo ($selected_round === 'all') ? 'selected' : ''; ?>>All Rounds</option>
              <?php foreach ($availableRounds as $round): ?>
              <option value="<?php echo $round; ?>" <?php echo ($selected_round === $round) ? 'selected' : ''; ?>><?php echo htmlspecialchars($round); ?></option>
              <?php endforeach; ?>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
              <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M10 13l-3-3h6l-3 3z"/></svg>
            </div>
          </div>
        </div>
      </div>
      <div class="flex items-center justify-between">
        <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full transition duration-150 ease-in-out transform hover:scale-105">
          Get Choice Filling List
        </button>
      </div>
    </form>
  </div>

  <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
    <?php if (!empty($spot_chances)): ?>
      <div class="card">
        <h2 class="text-2xl font-bold mb-6 text-center text-green-800">Spot Chances</h2>
        <?php foreach ($spot_chances as $row): ?>
          <div class="mb-4 p-4 border border-green-300 rounded-lg shadow-sm bg-green-50 highlight-card">
            <p><strong>Institute:</strong> <?php echo htmlspecialchars($row['Institute']); ?></p>
            <p><strong>Branch:</strong> <?php echo htmlspecialchars($row['Branch']); ?></p>
            <p><strong>Open Rank:</strong> <?php echo htmlspecialchars($row['Open']); ?></p>
            <p><strong>Close Rank:</strong> <?php echo htmlspecialchars($row['Close']); ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="card">
      <h2 class="text-2xl font-bold mb-6 text-center text-blue-800">Choice Filling List</h2>
      <?php if (!empty($error)): ?>
        <p class="text-red-500"><?php echo $error; ?></p>
      <?php else: ?>
        <?php
        $current_round = '';
        $displayed_colleges = [];
        foreach ($results as $row):
          if ($selected_round !== 'all' && $selected_round != $row['Round']) {
            continue;
          }
          if ($current_round !== $row['Round']):
            $current_round = $row['Round'];
        ?>
            <h3 class="text-xl font-semibold mb-4 text-blue-700">Round <?php echo htmlspecialchars($current_round); ?></h3>
        <?php endif; ?>
        <?php if (!in_array($row['Institute'], $displayed_colleges)): ?>
        <div class="mb-4 p-4 border border-blue-300 rounded-lg shadow-sm bg-blue-50">
          <p class="font-semibold text-lg"><strong>Institute:</strong> <?php echo htmlspecialchars($row['Institute']); ?></p>
          <p><strong>Branch:</strong> <?php echo htmlspecialchars($row['Branch']); ?></p>
          <p><strong>Open Rank:</strong> <?php echo htmlspecialchars($row['Open']); ?></p>
          <p><strong>Close Rank:</strong> <?php echo htmlspecialchars($row['Close']); ?></p>
        </div>
        <?php $displayed_colleges[] = $row['Institute']; ?>
        <?php endif; ?>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<!-- Footer -->
<footer class="bg-blue-800 text-white p-4 mt-8 shadow-lg">
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
  new Choices('#location', {
    removeItemButton: true,
    placeholder: true,
    placeholderValue: 'Select preferred locations'
  });
});
</script>

</body>
</html>
