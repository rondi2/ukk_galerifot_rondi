<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['status']) || $_SESSION['status'] != 'login') {
    echo "<script>
    alert('Anda Belum Login!');
    location.href='../index.php';
    </script>";
    exit();
}

if (isset($_POST['tambah'])) {
    // Handle file upload and form submission
    $judulfoto = $_POST['judulfoto'];
    $deskripsifoto = $_POST['deskripsifoto'];
    $albumid = intval($_POST['albumid']);
    $userid = $_SESSION['userid'];
    $tanggalunggah = date('Y-m-d');

    // File upload handling
    $foto = $_FILES['lokasifile']['name'];
    $tmp = $_FILES['lokasifile']['tmp_name'];
    $lokasi = '../assets/img/';
    $namafoto = rand() . '-' . basename($foto); // Full filename including extension

    if (move_uploaded_file($tmp, $lokasi . $namafoto)) {
        // Save the data and file information to the database
        $stmt = $koneksi->prepare("INSERT INTO foto (judulfoto, deskripsifoto, tanggalunggah, lokasifile, albumid, userid) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssii", $judulfoto, $deskripsifoto, $tanggalunggah, $namafoto, $albumid, $userid);
        if ($stmt->execute()) {
            echo "<script>
            alert('Data Berhasil Ditambahkan!');
            location.href='../admin/foto.php';
            </script>";
        } else {
            echo "<script>
            alert('Gagal menambahkan data!');
            location.href='../admin/foto.php';
            </script>";
        }
        $stmt->close();
    } else {
        echo "<script>
        alert('Gagal mengunggah file!');
        location.href='../admin/foto.php';
        </script>";
    }
}

if (isset($_POST['edit'])) {
    // Handle file update
    $fotoid = intval($_POST['fotoid']);
    $judulfoto = $_POST['judulfoto'];
    $deskripsifoto = $_POST['deskripsifoto'];
    $albumid = intval($_POST['albumid']);
    $tanggalunggah = date('Y-m-d');
    $foto = $_FILES['lokasifile']['name'];
    $tmp = $_FILES['lokasifile']['tmp_name'];
    $lokasi = '../assets/img/';
    $namafoto = rand() . '-' . basename($foto);

    if (empty($foto)) {
        // No file was uploaded, so just update other fields
        $stmt = $koneksi->prepare("UPDATE foto SET judulfoto=?, deskripsifoto=?, tanggalunggah=?, albumid=? WHERE fotoid=?");
        $stmt->bind_param("sssii", $judulfoto, $deskripsifoto, $tanggalunggah, $albumid, $fotoid);
    } else {
        // Update file and other fields
        $query = $koneksi->prepare("SELECT lokasifile FROM foto WHERE fotoid = ?");
        $query->bind_param("i", $fotoid);
        $query->execute();
        $result = $query->get_result();
        $data = $result->fetch_assoc();

        if ($data) {
            $oldFilePath = '../assets/img/' . $data['lokasifile'];
            if (is_file($oldFilePath)) {
                unlink($oldFilePath);
            }
        }

        if (move_uploaded_file($tmp, $lokasi . $namafoto)) {
            $stmt = $koneksi->prepare("UPDATE foto SET judulfoto=?, deskripsifoto=?, tanggalunggah=?, lokasifile=?, albumid=? WHERE fotoid=?");
            $stmt->bind_param("ssssii", $judulfoto, $deskripsifoto, $tanggalunggah, $namafoto, $albumid, $fotoid);
        } else {
            echo "<script>
            alert('Gagal mengunggah file!');
            location.href='../admin/foto.php';
            </script>";
            exit();
        }
    }

    if ($stmt->execute()) {
        echo "<script>
        alert('Data Berhasil Diperbarui!');
        location.href='../admin/foto.php';
        </script>";
    } else {
        echo "<script>
        alert('Gagal memperbarui data!');
        location.href='../admin/foto.php';
        </script>";
    }
    $stmt->close();
}

if (isset($_POST['hapus'])) {
    // Handle file deletion
    $fotoid = intval($_POST['fotoid']);
    $query = $koneksi->prepare("SELECT lokasifile FROM foto WHERE fotoid = ?");
    $query->bind_param("i", $fotoid);
    $query->execute();
    $result = $query->get_result();
    $data = $result->fetch_assoc();

    if ($data) {
        $filePath = '../assets/img/' . $data['lokasifile'];
        if (is_file($filePath)) {
            unlink($filePath);
        }
        $stmt = $koneksi->prepare("DELETE FROM foto WHERE fotoid=?");
        $stmt->bind_param("i", $fotoid);
        if ($stmt->execute()) {
            echo "<script>
            alert('Data Berhasil Dihapus!');
            location.href='../admin/foto.php';
            </script>";
        } else {
            echo "<script>
            alert('Gagal menghapus data!');
            location.href='../admin/foto.php';
            </script>";
        }
        $stmt->close();
    } else {
        echo "<script>
        alert('Data tidak ditemukan!');
        location.href='../admin/foto.php';
        </script>";
    }
}
?>
