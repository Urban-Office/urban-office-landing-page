<?php
/**
 * Shared Locations Showcase Component
 * Parameters:
 *  - $package_name: string (required, e.g. 'PT Badan', 'CV', 'PT Perorangan', 'PT PMA')
 */

$package_label = $package_name ?? 'Layanan Bisnis';

$bundle_locations = [
    [
        'title' => 'MERR (Surabaya Timur)',
        'address' => 'Jl. Dr. Ir. H. Soekarno No.470, Kedung Baruk, Kec. Rungkut, Surabaya, Jawa Timur 60298',
        'image' => BASE_URL . 'assets/images/branch/urban office - merr.jpeg'
    ],
    [
        'title' => 'Klampis (Surabaya Timur)',
        'address' => 'Ruko Klampis Megah, Jl. Klampis Jaya blok B-20, Klampis Ngasem, Kec. Sukolilo, Surabaya, Jawa Timur 60117',
        'image' => BASE_URL . 'assets/images/branch/klampis.png'
    ],
    [
        'title' => 'Grand Sungkono (Surabaya Barat)',
        'address' => 'PP54+MJW, Jl. KH Abdul Wahab Siamin Surabaya, Dukuh Pakis, Kec. Dukuhpakis, Surabaya, Jawa Timur 60225',
        'image' => BASE_URL . 'assets/images/branch/urban office - grand sungkono lagoon.webp'
    ],
    [
        'title' => 'Fatmawati (Jakarta Selatan)',
        'address' => 'Jl. RS. Fatmawati Raya No.35A 2, RT.2/RW.5, Cilandak Bar., Kec. Cilandak, Jakarta, Daerah Khusus Ibukota Jakarta 12430',
        'image' => BASE_URL . 'assets/images/branch/urban office fatmawati.jpg'
    ],
    [
        'title' => 'Gorebiz (Jakarta Timur)',
        'address' => 'Jl. Raya Bekasi.KM.17, RT.4/RW.3, Jatinegara, Kec. Cakung, Kota Jakarta Timur, Daerah Khusus Ibukota Jakarta 13930 Timur, RT.1/RW.3, Jatinegara Kaum, Kec. Pulo Gadung, Kota Jakarta Timur, Daerah Khusus Ibukota Jakarta 13930',
        'image' => BASE_URL . 'assets/images/branch/gorebiz.png'
    ],
    [
        'title' => 'PTGM Tower (Gresik)',
        'address' => 'Jl. Dr. Wahidin Sudirohusodo No.708, Kembangan, Kec. Kebomas, Kabupaten Gresik, Jawa Timur 61161',
        'image' => BASE_URL . 'assets/images/branch/ptgm.png'
    ],
    [
        'title' => 'Medan',
        'address' => 'Jl. Sutrisno No.258, Medan',
        'image' => BASE_URL . 'assets/images/branch/medan.png'
    ]
];
?>

<!-- Locations Showcase Section -->
<section class="location-sec">
    <div class="container text-center">
        <h2 class="section-title">Pilihan Alamat Kantor Prestisius</h2>
        <p class="section-subtitle">Pilih domisili gedung strategis berikut yang akan digunakan sebagai alamat legalitas resmi <?php echo htmlspecialchars($package_label); ?> Anda.</p>
        
        <div class="location-grid-small">
            <?php 
            foreach ($bundle_locations as $loc):
                $wa_message = "Halo Urban Office, saya ingin memilih alamat Virtual Office di " . $loc['title'] . " untuk paket bundling " . $package_label . ".";
                $wa_url = "https://api.whatsapp.com/send?phone=6285107620100&text=" . urlencode($wa_message);
            ?>
                <div class="loc-card">
                    <div class="loc-img-wrap">
                        <img src="<?php echo $loc['image']; ?>" alt="<?php echo htmlspecialchars($loc['title']); ?>" loading="lazy">
                    </div>
                    <div class="loc-body">
                        <h4><?php echo htmlspecialchars($loc['title']); ?></h4>
                        <p><?php echo htmlspecialchars($loc['address']); ?></p>
                    </div>
                    <div class="loc-footer">
                        <a href="<?php echo $wa_url; ?>" target="_blank" rel="noopener" class="btn btn-outline" style="padding: 10px; font-size: 0.8rem; width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 6px;"><i class="bi bi-whatsapp"></i> Pilih Alamat</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <p style="margin-top: 40px; font-size: 0.9rem; color: hsl(var(--clr-text-muted));">
            *Semua lokasi di atas dapat dipilih sebagai alamat domisili hukum <?php echo htmlspecialchars($package_label); ?> Anda. Untuk informasi detail fasilitas cabang, silakan hubungi tim legal kami atau kunjungi <a href="<?php echo BASE_URL; ?>lokasi-urban-office/" style="font-weight: 700; color: hsl(var(--clr-primary));">Halaman Lokasi &rarr;</a>
        </p>
    </div>
</section>

<style>
/* Locations page block styling */
.location-sec {
    padding: 80px 0;
    background-color: #FFFFFF;
}
.location-grid-small {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
    margin-top: 40px;
}
.loc-card {
    background-color: #FFFFFF;
    border-radius: var(--radius-md);
    border: 1px solid hsl(var(--clr-border));
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.loc-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
    border-color: hsl(var(--clr-primary));
}
.loc-img-wrap {
    height: 180px;
    overflow: hidden;
}
.loc-img-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}
.loc-card:hover .loc-img-wrap img {
    transform: scale(1.06);
}
.loc-body {
    padding: 20px;
    text-align: left;
    flex-grow: 1;
}
.loc-body h4 {
    font-size: 1.05rem;
    font-weight: 700;
    margin-bottom: 8px;
    color: #111111;
}
.loc-body p {
    font-size: 0.8rem;
    color: hsl(var(--clr-text-muted));
    line-height: 1.5;
    margin: 0;
}
.loc-footer {
    padding: 0 20px 20px 20px;
}

/* Center the button on tablet and desktop (min-width: 768px) */
@media (min-width: 768px) {
    .loc-footer {
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .loc-footer .btn {
        width: auto !important;
        min-width: 160px;
        padding: 10px 24px !important;
    }
}
</style>
