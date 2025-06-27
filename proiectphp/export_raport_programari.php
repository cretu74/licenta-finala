<?php
session_start();
setlocale(LC_ALL, 'en_US.UTF-8');
include("connection.php");
require('fpdf/fpdf.php');

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont("Arial", "B", 16);
$pdf->Cell(0, 10, "Raport programari", 0, 1, "C");
$pdf->Ln(2);

// Perioada (daca exista)
if (!empty($_GET['de_la']) && !empty($_GET['pana_la'])) {
    $perioada_de = date("d.m.Y", strtotime($_GET['de_la']));
    $perioada_pana = date("d.m.Y", strtotime($_GET['pana_la']));
    $pdf->SetFont("Arial", "", 11);
    $pdf->Cell(0, 8, "Perioada: $perioada_de - $perioada_pana", 0, 1, "C");
    $pdf->Ln(1);
}

$pdf->SetFont("Arial", "", 10);
$pdf->Cell(0, 10, "Data export: " . date("d.m.Y"), 0, 1, "R");

$where = "WHERE 1=1";

// Filtru pacient (ID)
if (!empty($_GET['pacient'])) {
    $pacient_id = (int)$_GET['pacient'];
    $where .= " AND pa.id = $pacient_id";
}

// Filtru medic (ID)
if (!empty($_GET['medic'])) {
    $medic_id = (int)$_GET['medic'];
    $where .= " AND m.id = $medic_id";
}

// Filtru status
if (!empty($_GET['status'])) {
    $status = mysqli_real_escape_string($con, $_GET['status']);
    $where .= " AND p.status = '$status'";
}

// Filtru perioada
if (!empty($_GET['de_la']) && !empty($_GET['pana_la'])) {
    $de = mysqli_real_escape_string($con, $_GET['de_la']);
    $pana = mysqli_real_escape_string($con, $_GET['pana_la']);
    $where .= " AND p.data BETWEEN '$de' AND '$pana'";
}

$query = "
    SELECT p.data, p.interval_orar, p.status, p.motiv,
           pa.nume AS pacient_nume,
           m.nume AS medic_nume, m.prenume AS medic_prenume
    FROM programari p
    JOIN pacienti pa ON p.pacient_id = pa.id
    JOIN medici m ON p.medic_id = m.id
    $where
    ORDER BY p.data DESC
";

$rez = mysqli_query($con, $query);

// Contori statusuri
$total = 0;
$neconfirmate = 0;
$confirmate = 0;
$finalizate = 0;
$anulate = 0;

// Header tabel
$pdf->SetFillColor(92, 107, 192);
$pdf->SetTextColor(255);
$pdf->SetFont("Arial", "B", 10);
$pdf->Cell(25, 10, "Zi", 1, 0, "C", true);
$pdf->Cell(25, 10, "Data", 1, 0, "C", true);
$pdf->Cell(30, 10, "Interval", 1, 0, "C", true);
$pdf->Cell(30, 10, "Pacient", 1, 0, "C", true);
$pdf->Cell(40, 10, "Medic", 1, 0, "C", true);
$pdf->Cell(25, 10, "Status", 1, 0, "C", true);
$pdf->Cell(15, 10, "Motiv", 1, 1, "C", true);

$pdf->SetTextColor(0);
$pdf->SetFont("Arial", "", 10);

while ($row = mysqli_fetch_assoc($rez)) {
    $total++;

    $status_raw = strtolower(trim($row['status']));
    $status = iconv('UTF-8', 'ASCII//TRANSLIT', $status_raw);

    if ($status == 'neconfirmata') $neconfirmate++;
    elseif ($status == 'confirmata') $confirmate++;
    elseif ($status == 'finalizata') $finalizate++;
    elseif ($status == 'anulata') $anulate++;

    $zi = date("l", strtotime($row['data']));
    $zi = [
        'Monday' => 'Luni', 'Tuesday' => 'Marti', 'Wednesday' => 'Miercuri',
        'Thursday' => 'Joi', 'Friday' => 'Vineri', 'Saturday' => 'Sambata', 'Sunday' => 'Duminica'
    ][$zi] ?? $zi;

    $pdf->Cell(25, 10, $zi, 1);
    $pdf->Cell(25, 10, date("d.m.Y", strtotime($row['data'])), 1);
    $pdf->Cell(30, 10, $row['interval_orar'], 1);
    $pdf->Cell(30, 10, $row['pacient_nume'], 1);
    $pdf->Cell(40, 10, $row['medic_nume'] . ' ' . $row['medic_prenume'], 1);
    $pdf->Cell(25, 10, ucfirst($row['status']), 1);
    $pdf->Cell(15, 10, $row['motiv'], 1, 1);
}

// Statistici
$pdf->Ln(10);
$pdf->SetFont("Arial", "B", 10);
$pdf->Cell(0, 8, "Total programari afisate: $total", 0, 1);
$pdf->SetFont("Arial", "", 10);
$pdf->Cell(0, 6, "Neconfirmate: $neconfirmate", 0, 1);
$pdf->Cell(0, 6, "Confirmate: $confirmate", 0, 1);
$pdf->Cell(0, 6, "Finalizate: $finalizate", 0, 1);
$pdf->Cell(0, 6, "Anulate: $anulate", 0, 1);

$pdf->Output("I", "raport_programari.pdf");
?>
