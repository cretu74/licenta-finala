<?php
require('fpdf/fpdf.php');
include("connection.php");

$where = "WHERE 1=1";

if (!empty($_GET['nume'])) {
    $nume = mysqli_real_escape_string($con, $_GET['nume']);
    $where .= " AND p.nume = '" . $nume . "'";
}
if (!empty($_GET['specie'])) {
    $specie = mysqli_real_escape_string($con, $_GET['specie']);
    $where .= " AND p.specie LIKE '%$specie%'";
}
if (!empty($_GET['proprietar'])) {
    $proprietar = mysqli_real_escape_string($con, $_GET['proprietar']);
    $where .= " AND CONCAT(pr.nume, ' ', pr.prenume) = '" . $proprietar . "'";
}
if (!empty($_GET['de_la']) && !empty($_GET['pana_la'])) {
    $de_la = mysqli_real_escape_string($con, $_GET['de_la']);
    $pana_la = mysqli_real_escape_string($con, $_GET['pana_la']);
    $where .= " AND p.data_inregistrare BETWEEN '$de_la' AND '$pana_la'";
}

$query = "
    SELECT p.id, p.nume AS pacient_nume, p.specie, p.rasa, p.data_inregistrare,
           pr.nume AS prop_nume, pr.prenume AS prop_prenume
    FROM pacienti p
    JOIN proprietari pr ON p.proprietar_id = pr.id
    $where
    ORDER BY p.data_inregistrare DESC
";
$rezultat = mysqli_query($con, $query);

class PDF extends FPDF {
    function Header() {
        // optional
    }
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Raport pacienti', 0, 1, 'C');

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 8, 'Data export: ' . date("d.m.Y"), 0, 1, 'C');
$pdf->Ln(5);

// Tabel
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(220, 220, 255); // fundal pentru titluri
$pdf->Cell(35, 10, 'Nume pacient', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Specie', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Rasa', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Proprietar', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Data inreg.', 1, 0, 'C', true);
$pdf->Cell(15, 10, 'Serv', 1, 0, 'C', true);
$pdf->Cell(15, 10, 'Plati', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 10);
$total_pacienti = 0;
$total_servicii = 0;
$total_plati = 0;

while ($row = mysqli_fetch_assoc($rezultat)) {
    $total_pacienti++;
    $pacient_id = $row['id'];
    $nr_servicii = mysqli_num_rows(mysqli_query($con, "SELECT id FROM servicii WHERE pacient_id = $pacient_id"));
    $nr_plati = mysqli_num_rows(mysqli_query($con, "SELECT p.id FROM plati p JOIN servicii s ON p.serviciu_id = s.id WHERE s.pacient_id = $pacient_id"));
    $total_servicii += $nr_servicii;
    $total_plati += $nr_plati;

    $pdf->Cell(35, 8, $row['pacient_nume'], 1);
    $pdf->Cell(25, 8, $row['specie'], 1);
    $pdf->Cell(30, 8, $row['rasa'], 1);
    $pdf->Cell(40, 8, $row['prop_nume'] . ' ' . $row['prop_prenume'], 1);
    $pdf->Cell(30, 8, date("d.m.Y", strtotime($row['data_inregistrare'])), 1);
    $pdf->Cell(15, 8, $nr_servicii, 1, 0, 'C');
    $pdf->Cell(15, 8, $nr_plati, 1, 1, 'C');
}

// totaluri
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(160, 8, 'Total servicii/plati:', 1);
$pdf->Cell(15, 8, $total_servicii, 1, 0, 'C');
$pdf->Cell(15, 8, $total_plati, 1, 1, 'C');

$pdf->Ln(4);
$pdf->Cell(0, 8, 'Numar total pacienti afisati: ' . $total_pacienti, 0, 1);

$pdf->Output("I", "raport_pacienti.pdf");
exit;
