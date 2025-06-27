<?php
session_start();
require('fpdf/fpdf.php');
include("connection.php");

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont("Arial", "B", 16);
$pdf->Cell(0, 10, "Raport incasari", 0, 1, "C");
$pdf->Ln(5);

// Data exportului
$pdf->SetFont("Arial", "", 10);
$pdf->Cell(0, 10, "Data export: " . date("d.m.Y"), 0, 1, "R");

$where = "WHERE 1=1";

// Filtrare după serviciu
if (!empty($_GET['serviciu'])) {
    $serviciu = mysqli_real_escape_string($con, $_GET['serviciu']);
    $where .= " AND s.denumire LIKE '%$serviciu%'";
}

// Filtrare după pacient (id)
if (!empty($_GET['pacient'])) {
    $pacient = mysqli_real_escape_string($con, $_GET['pacient']);
    $where .= " AND pa.id = '$pacient'";
}

// Filtrare după medic (id)
if (!empty($_GET['medic'])) {
    $medic = mysqli_real_escape_string($con, $_GET['medic']);
    $where .= " AND m.id = '$medic'";
}

// Filtrare după sumă
if (!empty($_GET['suma'])) {
    $suma = (float)$_GET['suma'];
    $where .= " AND p.suma = $suma";
}

// Filtrare după dată (de la - până la)
if (!empty($_GET['de_la']) && !empty($_GET['pana_la'])) {
    $de = mysqli_real_escape_string($con, $_GET['de_la']);
    $pana = mysqli_real_escape_string($con, $_GET['pana_la']);
    $where .= " AND p.data_plata BETWEEN '$de' AND '$pana'";
}

$query = "
    SELECT p.*, s.denumire AS serviciu, pa.nume AS pacient, m.nume AS medic_nume, m.prenume AS medic_prenume
    FROM plati p
    JOIN servicii s ON p.serviciu_id = s.id
    JOIN pacienti pa ON s.pacient_id = pa.id
    JOIN medici m ON s.medic_id = m.id
    $where
    ORDER BY p.data_plata DESC
";
$rez = mysqli_query($con, $query);

// Header tabel
$pdf->SetFillColor(92, 107, 192);
$pdf->SetTextColor(255);
$pdf->SetFont("Arial", "B", 10);
$pdf->Cell(50, 10, "Denumire serviciu", 1, 0, "C", true);
$pdf->Cell(35, 10, "Pacient", 1, 0, "C", true);
$pdf->Cell(40, 10, "Medic", 1, 0, "C", true);
$pdf->Cell(25, 10, "Suma", 1, 0, "C", true);
$pdf->Cell(35, 10, "Data platii", 1, 1, "C", true);

// Randuri rezultate
$pdf->SetTextColor(0);
$pdf->SetFont("Arial", "", 10);
$total_suma = 0;

while ($row = mysqli_fetch_assoc($rez)) {
    $pdf->Cell(50, 10, $row['serviciu'], 1);
    $pdf->Cell(35, 10, $row['pacient'], 1);
    $pdf->Cell(40, 10, $row['medic_nume'] . ' ' . $row['medic_prenume'], 1);
    $pdf->Cell(25, 10, number_format($row['suma'], 2) . " lei", 1, 0, "R");
    $pdf->Cell(35, 10, date("d.m.Y", strtotime($row['data_plata'])), 1, 1);
    $total_suma += $row['suma'];
}

// Total
$pdf->Ln(5);
$pdf->SetFont("Arial", "B", 11);
$pdf->Cell(0, 10, "Total incasari: " . number_format($total_suma, 2) . " lei", 0, 1, "R");

$pdf->Output("I", "raport_incasari.pdf");
?>