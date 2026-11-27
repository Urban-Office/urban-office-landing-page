# Google Ads — Quality Score Baseline (SEBELUM deploy improvement)

- **Akun:** Urban Account (846-570-9843) di bawah MCC "urban office" (226-975-8268)
- **Tanggal tarik:** 2026-07-29 · **Rentang:** 30 hari terakhir · **Channel:** Search
- **Tujuan:** patokan pembanding. Tarik ulang query yang sama ~2–4 minggu setelah deploy untuk melihat perubahan Landing Page Experience (LPE) & Quality Score (QS).

> Kolom: QS = Quality Score (1–10) · LPE = Landing Page Experience (post-click) · AdRel = Ad Relevance · CTR = Expected CTR · Impr = impresi 30 hari.
> QS `0`/UNSPECIFIED = data belum cukup (keyword baru / volume rendah) — belum bisa dinilai.

## A. VIRTUAL OFFICE

### Jakarta — TARGET UTAMA (LPE Below Average)
| Keyword | QS | LPE | AdRel | CTR | Impr |
|---|---|---|---|---|---|
| virtual office | **5** | **BELOW_AVERAGE** | Above | Avg | 161 |
| sewa kantor virtual office | 0 | (belum ada data) | – | – | 227 |

### Surabaya — sudah sehat (jangan sampai turun)
| Keyword | QS | LPE | Impr |
|---|---|---|---|
| kantor virtual surabaya | 10 | ABOVE_AVERAGE | 62 |
| virtual office di surabaya | 10 / 8 | ABOVE / AVERAGE | 36–152 |
| virtual office surabaya | 8 | AVERAGE | 41–129 |
| virtual office surabaya barat | 8 | AVERAGE | 36 |
| urban office | 10 | ABOVE_AVERAGE | 51 |
| urban office klampis | 6 | ABOVE_AVERAGE | 110 |
| kantor virtual | 7 | AVERAGE | 188 |

## B. PRIVATE OFFICE / SEWA KANTOR — banyak LPE Below Average
| Keyword | Kota | QS | LPE | Impr |
|---|---|---|---|---|
| sewa kantor surabaya | SBY | 8 | AVERAGE | 271 |
| working space surabaya | SBY | 4 | BELOW_AVERAGE | 438 |
| working space | SBY | 1 | BELOW_AVERAGE | 334 |
| office surabaya | SBY | 3 | BELOW_AVERAGE | 145 |
| kantor surabaya | SBY | 3 | BELOW_AVERAGE | 132 |
| sewa kantor | SBY | 5 | BELOW_AVERAGE | 76 |
| surabaya office | SBY | 7 | BELOW_AVERAGE | 93 |
| kantor perusahaan | SBY | 1 | BELOW_AVERAGE | 85 |
| working space | JKT | 1 | BELOW_AVERAGE | 48 |
| kantor jakarta | JKT | 0 | (belum ada data) | 144+46 |

## C. MEETING ROOM (Surabaya) — banyak LPE Below Average
| Keyword | QS | LPE | Impr |
|---|---|---|---|
| sewa ruang pertemuan | 7 | BELOW_AVERAGE | 329 |
| sewa meeting room surabaya | 6 | BELOW_AVERAGE | 61 |
| meeting room surabaya | 4 | BELOW_AVERAGE | 48 |
| ruang meeting surabaya | 4 | BELOW_AVERAGE | 37 |
| tempat rapat surabaya | 5 | BELOW_AVERAGE | 35 |
| meeting space surabaya | 3 | BELOW_AVERAGE | 49 |

## Ringkasan LPE (keyword yang sudah punya QS)
- **Below Average:** ~17 keyword — didominasi **Private Office/Office** & **Meeting Room**, plus **Virtual Office Jakarta**.
- **Average:** ~19 keyword — mayoritas **Virtual Office Surabaya**.
- **Above Average:** ~6 keyword — keyword brand & VO Surabaya inti.

## Query untuk tarik-ulang (bandingkan nanti)
- resource: `keyword_view`
- fields: campaign.name, ad_group.name, ad_group_criterion.keyword.text, quality_info.quality_score, quality_info.post_click_quality_score, quality_info.creative_quality_score, quality_info.search_predicted_ctr, metrics.impressions, metrics.clicks
- conditions: `segments.date DURING LAST_30_DAYS`, `ad_group_criterion.status = 'ENABLED'`, `campaign.advertising_channel_type = 'SEARCH'`
- order: metrics.impressions DESC
