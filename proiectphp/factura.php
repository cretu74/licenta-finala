<?php
require('fpdf/fpdf.php');
include("connection.php");

if (!isset($_GET['id_plata'])) {
    die("Lipsă ID plată.");
}

$id = (int)$_GET['id_plata'];
$res = mysqli_query($con, "
    SELECT p.*, s.denumire, pa.nume AS pacient, m.nume AS medic_nume, m.prenume AS medic_prenume
    FROM plati p
    JOIN servicii s ON p.serviciu_id = s.id
    JOIN pacienti pa ON s.pacient_id = pa.id
    JOIN medici m ON s.medic_id = m.id
    WHERE p.id = $id
");

if (!$res || mysqli_num_rows($res) == 0) {
    die("Plata nu a fost găsită.");
}

$row = mysqli_fetch_assoc($res);

// Generează PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',12);

// Colțul sus-stânga – date firmă
$pdf->Cell(100, 6, "SPS Vet", 0, 1);
$pdf->SetFont('Arial','',10);
$pdf->Cell(100, 5, "Reg. com.: J27/79/2011", 0, 1);
$pdf->Cell(100, 5, "Adresa: Strada brasov nr 25, SECTOR1", 0, 1);
$pdf->Cell(100, 5, "CIF: RO28011247", 0, 1);
$pdf->Cell(100, 5, "Cont: RO41INGB0000999909298011", 0, 1);
$pdf->Cell(100, 5, "Banca: ING", 0, 1);

$pdf->Ln(15);

// Titlu central
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0, 10, 'FACTURA', 0, 1, 'C');
$pdf->Ln(5);

// Informații factură
$pdf->SetFont('Arial','',11);
$pdf->Cell(0, 8, "Data emiterii: " . date("d.m.Y", strtotime($row['data_plata'])), 0, 1);
$pdf->Cell(0, 8, "Pacient: " . $row['pacient'], 0, 1);
$pdf->Cell(0, 8, "Medic: " . $row['medic_nume'] . ' ' . $row['medic_prenume'], 0, 1);

$pdf->Ln(10);

// Tabel servicii
$pdf->SetFont('Arial','B',11);
$pdf->Cell(120, 10, 'Serviciu', 1);
$pdf->Cell(40, 10, 'Pret', 1);
$pdf->Ln();

$pdf->SetFont('Arial','',11);
$pdf->Cell(120, 10, $row['denumire'], 1);
$pdf->Cell(40, 10, number_format($row['suma'], 2) . " lei", 1);
$pdf->Ln(20);

// Total și semnătură
$pdf->SetFont('Arial','B',12);
$pdf->Cell(120, 10, 'Total de plata:', 0);
$pdf->Cell(40, 10, number_format($row['suma'], 2) . " lei", 0);
$pdf->Ln(20);

$pdf->SetFont('Arial','',10);
$pdf->Cell(0, 10, 'Semnatura si stampila furnizor', 0, 1);

$pdf->Output('I', 'Factura_' . $row['id'] . '.pdf');
