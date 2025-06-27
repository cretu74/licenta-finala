<?php
session_start();
include("connection.php");
include("functions.php");

// Afișare detalii pacient
$view_pacient = null;
$edit_mode = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salveaza'])) {
    $stmt = $con->prepare("UPDATE pacienti SET numar_fisa=?, data_inregistrare=?, proprietar_id=?, nume=?, specie=?, rasa=?, sex=?, greutate=?, culoare=?, varsta=?, microcip=?, boli_cronice=? WHERE id=?");
    $stmt->bind_param("ssssssssssssi",
        $_POST['numar_fisa'], $_POST['data_inregistrare'], $_POST['proprietar_id'], $_POST['nume'],
        $_POST['specie'], $_POST['rasa'], $_POST['sex'], $_POST['greutate'],
        $_POST['culoare'], $_POST['varsta'], $_POST['microcip'], $_POST['boli_cronice'], $_POST['id']
    );
    $stmt->execute();
    header("Location: pacienti.php?id={$_POST['id']}");
    exit();
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $res = mysqli_query($con, "SELECT * FROM pacienti WHERE id = $id LIMIT 1");
    $view_pacient = mysqli_fetch_assoc($res);
    $edit_mode = isset($_GET['edit']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Detalii pacient</title>
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f4f7fc url("imagine.png") no-repeat center center fixed;
        background-size: cover;
        margin: 0;
        padding: 30px;
    }

    .container {
        max-width: 1100px;
        margin: auto;
        background: #fff;
        border-radius: 10px;
        padding: 30px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }

    h1, h2 {
        color: #333;
        margin-bottom: 20px;
        border-bottom: 2px solid #5c6bc0;
        padding-bottom: 5px;
    }

    form {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-top: 10px;
    }

    .form-group {
        flex: 1 1 calc(50% - 20px);
        display: flex;
        flex-direction: column;
    }

    label {
        font-weight: 600;
        margin-bottom: 6px;
        color: #444;
    }

    input, select, textarea {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
    }

    textarea {
        resize: vertical;
        min-height: 60px;
    }

    .btn {
        padding: 10px 20px;
        background-color: #5c6bc0;
        color: #fff;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        text-decoration: none;
        font-weight: bold;
        margin-top: 10px;
        display: inline-block;
    }

    .btn:hover {
        background-color: #3f51b5;
    }

    .btn.cancel {
        background-color: #999;
    }

    .btn.cancel:hover {
        background-color: #777;
    }

    .btn.red {
        background-color: #e53935;
    }

    .btn.red:hover {
        background-color: #c62828;
    }

    .btn.purple {
        background-color: #8e24aa;
    }

    .btn.purple:hover {
        background-color: #6a1b9a;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 25px;
    }

    table th, table td {
        padding: 12px 10px;
        border: 1px solid #ddd;
        text-align: left;
    }

    table th {
         background: #5c6bc0;
    color: white;;
        font-weight: bold;
    }

    table td {
        background-color: #fafafa;
    }

    hr {
        margin: 40px 0 20px;
        border: none;
        border-top: 2px solid #ddd;
    }

    textarea {
        font-family: inherit;
    }

    @media screen and (max-width: 768px) {
        .form-group {
            flex: 1 1 100%;
        }
    }
</style>

</head>
<body>
<div class="container">
    <?php if ($view_pacient): ?>
        <h1><?= $edit_mode ? "Editare pacient" : "Date pacient" ?></h1>

        <?php if ($edit_mode): ?>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $view_pacient['id'] ?>">
            <div class="form-group"><label>Nr. fișă *</label><input name="numar_fisa" required value="<?= $view_pacient['numar_fisa'] ?>"></div>
            <div class="form-group"><label>Data înregistrare *</label><input type="date" name="data_inregistrare" required value="<?= $view_pacient['data_inregistrare'] ?>"></div>
            <div class="form-group"><label>Proprietar *</label>
                <select name="proprietar_id" required>
                    <option value="">-- Selectează --</option>
                    <?php
                    $prop_res = mysqli_query($con, "SELECT id, nume, prenume FROM proprietari");
                    while ($p = mysqli_fetch_assoc($prop_res)):
                        $selected = ($p['id'] == $view_pacient['proprietar_id']) ? 'selected' : '';
                        echo "<option value='{$p['id']}' $selected>{$p['nume']} {$p['prenume']}</option>";
                    endwhile;
                    ?>
                </select>
            </div>
            <div class="form-group"><label>Nume animal *</label><input name="nume" required value="<?= $view_pacient['nume'] ?>"></div>
            <div class="form-group"><label>Specie</label><input name="specie" value="<?= $view_pacient['specie'] ?>"></div>
            <div class="form-group"><label>Rasa</label><input name="rasa" value="<?= $view_pacient['rasa'] ?>"></div>
            <div class="form-group"><label>Sex</label>
                <select name="sex">
                    <option value="M" <?= $view_pacient['sex'] === 'M' ? 'selected' : '' ?>>M</option>
                    <option value="F" <?= $view_pacient['sex'] === 'F' ? 'selected' : '' ?>>F</option>
                </select>
            </div>
            <div class="form-group"><label>Greutate</label><input name="greutate" value="<?= $view_pacient['greutate'] ?>"></div>
            <div class="form-group"><label>Culoare</label><input name="culoare" value="<?= $view_pacient['culoare'] ?>"></div>
            <div class="form-group"><label>Vârstă</label><input name="varsta" value="<?= $view_pacient['varsta'] ?>"></div>
            <div class="form-group"><label>Microcip</label><input name="microcip" value="<?= $view_pacient['microcip'] ?>"></div>
            <div class="form-group" style="flex: 1 1 100%"><label>Boli cronice</label><textarea name="boli_cronice"><?= $view_pacient['boli_cronice'] ?></textarea></div>
            <button class="btn" type="submit" name="salveaza">Salvează</button>
            <a href="lista_pacienti.php" class="btn cancel">Renunță</a>
        </form>

        <?php else: ?>
        <?php
        $prop_nume = "Nespecificat";
        if ($view_pacient['proprietar_id']) {
            $get_prop = mysqli_query($con, "SELECT nume, prenume FROM proprietari WHERE id = {$view_pacient['proprietar_id']} LIMIT 1");
            if ($row = mysqli_fetch_assoc($get_prop)) {
                $prop_nume = $row['nume'] . ' ' . $row['prenume'];
            }
        }
        ?>
        <form>
            <div class="form-group"><label>Nr. fișă</label><input disabled value="<?= $view_pacient['numar_fisa'] ?>"></div>
            <div class="form-group"><label>Data înregistrare</label><input disabled value="<?= $view_pacient['data_inregistrare'] ?>"></div>
            <div class="form-group"><label>Proprietar</label><input disabled value="<?= $prop_nume ?>"></div>
            <div class="form-group"><label>Nume animal</label><input disabled value="<?= $view_pacient['nume'] ?>"></div>
            <div class="form-group"><label>Specie</label><input disabled value="<?= $view_pacient['specie'] ?>"></div>
            <div class="form-group"><label>Rasa</label><input disabled value="<?= $view_pacient['rasa'] ?>"></div>
            <div class="form-group"><label>Sex</label><input disabled value="<?= $view_pacient['sex'] ?>"></div>
            <div class="form-group"><label>Greutate</label><input disabled value="<?= $view_pacient['greutate'] ?> kg"></div>
            <div class="form-group"><label>Culoare</label><input disabled value="<?= $view_pacient['culoare'] ?>"></div>
            <div class="form-group"><label>Vârstă</label><input disabled value="<?= $view_pacient['varsta'] ?> ani"></div>
            <div class="form-group"><label>Microcip</label><input disabled value="<?= $view_pacient['microcip'] ?>"></div>
            <div class="form-group" style="flex: 1 1 100%"><label>Boli cronice</label><textarea disabled><?= $view_pacient['boli_cronice'] ?></textarea></div>
            <a href="pacienti.php?id=<?= $view_pacient['id'] ?>&edit=1" class="btn">Editează datele pacientului</a>
            <a href="pacienti.php?id=<?= $view_pacient['id'] ?>&servicii=1" class="btn">Vezi servicii</a>
            <a href="pacienti.php?id=<?= $view_pacient['id'] ?>&plati=1" class="btn">Vezi plăți</a>
            <a href="pacienti.php?id=<?= $view_pacient['id'] ?>&feedbacks=1" class="btn">Vezi feedbacks</a>



            <a href="lista_pacienti.php" class="btn cancel">Înapoi la listă</a>
        </form>
        <?php endif; ?>
    <?php else: ?>
        <p>Pacientul nu a fost găsit.</p>
        <a href="lista_pacienti.php" class="btn cancel">Înapoi</a>
    <?php endif; ?>

    <?php
// Adăugare serviciu fără cantitate
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adauga_serviciu'])) {
    $stmt = $con->prepare("INSERT INTO servicii (pacient_id, medic_id, denumire, pret, data_efectuare) VALUES (?, ?, ?, ?, ?)");
   $stmt->bind_param("iisss", $view_pacient['id'], $_POST['medic_id'], $_POST['denumire'], $_POST['pret'], $_POST['data_efectuare']);
    $stmt->execute();
    header("Location: pacienti.php?id={$view_pacient['id']}&servicii=1");
    exit();
}
// Salvare serviciu editat fără cantitate
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salveaza_serviciu'])) {
    $stmt = $con->prepare("UPDATE servicii SET denumire=?, pret=?, data_efectuare=?, medic_id=? WHERE id=?");
    $stmt->bind_param("sdsii", $_POST['denumire'], $_POST['pret'], $_POST['data_efectuare'], $_POST['medic_id'], $_POST['id_serviciu']);
    $stmt->execute();
    header("Location: pacienti.php?id={$view_pacient['id']}&servicii=1");
    exit();
}




// Ștergere serviciu
if (isset($_GET['sterge_serviciu'])) {
    $id_serviciu = (int)$_GET['sterge_serviciu'];
    mysqli_query($con, "DELETE FROM servicii WHERE id = $id_serviciu");
    header("Location: pacienti.php?id={$view_pacient['id']}&servicii=1");
    exit();
}

// Adăugare plată
if (isset($_GET['plateste'])) {
    $id_serviciu = (int)$_GET['plateste'];
    $serviciu = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM servicii WHERE id = $id_serviciu"));
    if ($serviciu) {
        $stmt = $con->prepare("INSERT INTO plati (pacient_id, serviciu_id, suma, data_plata) VALUES (?, ?, ?, CURDATE())");
        $stmt->bind_param("iid", $serviciu['pacient_id'], $serviciu['id'], $serviciu['pret']);
        $stmt->execute();
    }
    header("Location: pacienti.php?id={$serviciu['pacient_id']}&plati=1");
    exit();
}

// Ștergere plată
if (isset($_GET['sterge_plata'])) {
    $id_plata = (int)$_GET['sterge_plata'];
    mysqli_query($con, "DELETE FROM plati WHERE id = $id_plata");
    header("Location: pacienti.php?id={$view_pacient['id']}&plati=1");
    exit();
}

?>

 


<?php if (isset($_GET['servicii'])): ?>
    <hr>
    <h2>Servicii efectuate</h2>

    <!-- Formular adăugare -->
    <form method="POST">
        <div class="form-group"><label>Denumire</label><input name="denumire" required></div>
        <div class="form-group"><label>Preț</label><input type="number" name="pret" step="0.01" required></div>
        <div class="form-group"><label>Data efectuare</label><input type="date" name="data_efectuare" required></div>
        <div class="form-group"><label>Medic</label>
            <select name="medic_id" required>
                <option value="">-- Selectează medic --</option>
                <?php
                $medici = mysqli_query($con, "SELECT id, nume, prenume FROM medici");
                while ($m = mysqli_fetch_assoc($medici)) {
                    echo "<option value='{$m['id']}'>{$m['nume']} {$m['prenume']}</option>";
                }
                ?>
            </select>
        </div>
        <button type="submit" name="adauga_serviciu" class="btn">Adaugă serviciu</button>
    </form>

    <!-- Tabel servicii -->
    <table>
        <thead>
            <tr>
                <th>Denumire</th>
                <th>Preț</th>
                <th>Dată</th>
                <th>Medic</th>
                <th>Acțiuni</th>
            </tr>
        </thead>
        <tbody>
<?php
$servicii = mysqli_query($con, "
    SELECT s.*, m.nume, m.prenume
    FROM servicii s
    JOIN medici m ON s.medic_id = m.id
    WHERE s.pacient_id = {$view_pacient['id']}
    ORDER BY s.data_efectuare DESC
");
while ($s = mysqli_fetch_assoc($servicii)):
    $edit = isset($_GET['edit_serviciu']) && $_GET['edit_serviciu'] == $s['id'];
?>
    <?php if ($edit): ?>
    <form method="POST">
        <input type="hidden" name="id_serviciu" value="<?= $s['id'] ?>">
        <tr>
            <td><input name="denumire" value="<?= htmlspecialchars($s['denumire']) ?>" required></td>
            <td><input type="number" name="pret" step="0.01" value="<?= $s['pret'] ?>" required></td>
            <td><input type="date" name="data_efectuare" value="<?= $s['data_efectuare'] ?>" required></td>
            <td>
                <select name="medic_id" required>
                    <?php
                    $all_medici = mysqli_query($con, "SELECT id, nume, prenume FROM medici");
                    while ($m = mysqli_fetch_assoc($all_medici)) {
                        $sel = $m['id'] == $s['medic_id'] ? 'selected' : '';
                        echo "<option value='{$m['id']}' $sel>{$m['nume']} {$m['prenume']}</option>";
                    }
                    ?>
                </select>
            </td>
            <td>
                <button type="submit" name="salveaza_serviciu" class="btn">Salvează</button>
                <a href="pacienti.php?id=<?= $view_pacient['id'] ?>&servicii=1" class="btn cancel">Renunță</a>
            </td>
        </tr>
    </form>
    <?php else: ?>
    <tr>
        <td><?= htmlspecialchars($s['denumire']) ?></td>
        <td><?= number_format($s['pret'], 2) ?> lei</td>
        <td><?= $s['data_efectuare'] ?></td>
        <td><?= $s['nume'] . ' ' . $s['prenume'] ?></td>
        <td>
            <a href="pacienti.php?id=<?= $view_pacient['id'] ?>&edit_serviciu=<?= $s['id'] ?>&servicii=1" class="btn">Editare</a>
            <a href="pacienti.php?id=<?= $view_pacient['id'] ?>&sterge_serviciu=<?= $s['id'] ?>&servicii=1" class="btn cancel" onclick="return confirm('Ștergi serviciul?')">Șterge</a>
        </td>
    </tr>
    <?php endif; ?>
<?php endwhile; ?>
</tbody>


    </table>
<?php endif; ?>


<?php if (isset($_GET['plati'])): ?>
    <hr>
    <h2>Plăți</h2>
    <table>
        <thead>
            <tr>
                <th>Denumire serviciu</th>
                <th>Preț</th>
                <th>Stare</th>
                <th>Acțiuni</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $servicii = mysqli_query($con, "
            SELECT s.*, p.id AS plata_id, p.suma, p.data_plata
            FROM servicii s
            LEFT JOIN plati p ON s.id = p.serviciu_id
            WHERE s.pacient_id = {$view_pacient['id']}
            ORDER BY s.data_efectuare DESC
        ");
        while ($s = mysqli_fetch_assoc($servicii)):
        ?>
            <tr>
                <td><?= htmlspecialchars($s['denumire']) ?></td>
                <td><?= number_format($s['pret'], 2) ?> lei</td>
                <td>
                    <?php if ($s['plata_id']): ?>
                        Plătit pe <?= $s['data_plata'] ?>
                    <?php else: ?>
                        Neplătit
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($s['plata_id']): ?>
                        <a href="pacienti.php?id=<?= $view_pacient['id'] ?>&sterge_plata=<?= $s['plata_id'] ?>&plati=1" class="btn cancel" onclick="return confirm('Ștergi această plată?')">Șterge plata</a>
                        <a href="factura.php?id_plata=<?= $s['plata_id'] ?>" class="btn">Generare factură</a>
                    <?php else: ?>
                        <a href="pacienti.php?plateste=<?= $s['id'] ?>&id=<?= $view_pacient['id'] ?>&plati=1" class="btn">înregistrare plată</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php
// Adăugare sau actualizare feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salveaza_feedback'])) {
    $programare_id = (int)$_POST['programare_id'];
    $text = trim($_POST['text']);
    if (!empty($text)) {
        $existing = mysqli_query($con, "SELECT id FROM feedbacks WHERE programare_id = $programare_id");
        if (mysqli_num_rows($existing) > 0) {
            $stmt = $con->prepare("UPDATE feedbacks SET text = ?, data_adaugare = CURDATE() WHERE programare_id = ?");
            $stmt->bind_param("si", $text, $programare_id);
        } else {
            $stmt = $con->prepare("INSERT INTO feedbacks (programare_id, text, data_adaugare) VALUES (?, ?, CURDATE())");
            $stmt->bind_param("is", $programare_id, $text);
        }
        $stmt->execute();
    }
    header("Location: pacienti.php?id={$view_pacient['id']}&feedbacks=1");
    exit();
}

// Ștergere feedback
if (isset($_GET['sterge_feedback'])) {
    $fid = (int)$_GET['sterge_feedback'];
    mysqli_query($con, "DELETE FROM feedbacks WHERE id = $fid");
    header("Location: pacienti.php?id={$view_pacient['id']}&feedbacks=1");
    exit();
}
?>

<?php if (isset($_GET['feedbacks'])): ?>
    <hr>
    <h2>Feedbackuri programări</h2>
    <table>
        <thead>
            <tr>
                <th>Dată</th>
                <th>Interval</th>
                <th>Medic</th>
                <th>Feedback</th>
                <th>Acțiuni</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $res = mysqli_query($con, "
            SELECT p.id AS prog_id, p.data, p.interval_orar, m.nume, m.prenume, f.id AS fid, f.text
            FROM programari p
            LEFT JOIN feedbacks f ON p.id = f.programare_id
            JOIN medici m ON p.medic_id = m.id
            WHERE p.pacient_id = {$view_pacient['id']}
            ORDER BY p.data DESC
        ");
        while ($row = mysqli_fetch_assoc($res)):
        ?>
            <tr>
                <td><?= date("d.m.Y", strtotime($row['data'])) ?></td>
                <td><?= htmlspecialchars($row['interval_orar']) ?></td>
                <td><?= $row['nume'] . ' ' . $row['prenume'] ?></td>
                <td>
                    <form method="POST" style="display:inline-block;">
                        <input type="hidden" name="programare_id" value="<?= $row['prog_id'] ?>">
                        <textarea name="text"><?= htmlspecialchars($row['text'] ?? '') ?></textarea>
                        <button name="salveaza_feedback" class="btn purple"><?= $row['fid'] ? 'Salvează' : 'Adaugă' ?></button>
                    </form>
                </td>
                <td>
                    <?php if ($row['fid']): ?>
                        <a href="pacienti.php?id=<?= $view_pacient['id'] ?>&sterge_feedback=<?= $row['fid'] ?>&feedbacks=1" class="btn red" onclick="return confirm('Ștergi feedback-ul?')">Șterge</a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
<?php endif; ?>



</div>
</body>
</html>