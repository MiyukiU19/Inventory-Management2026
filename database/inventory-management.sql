-- ==========================================
-- 0. データベースの作成と選択
-- ==========================================
CREATE DATABASE IF NOT EXISTS `inventory-management`;
USE `inventory-management`;

-- 外部キー制約チェックを一時的に無効化（テーブル削除時のエラー防止）
SET FOREIGN_KEY_CHECKS = 0;

-- ==========================================
-- 1. テーブルの初期化 (削除)
-- ==========================================
DROP TABLE IF EXISTS possess_mat;
DROP TABLE IF EXISTS inventory_history;
DROP TABLE IF EXISTS inventory;
DROP TABLE IF EXISTS mat_specification;
DROP TABLE IF EXISTS user;

-- ==========================================
-- 2. テーブルの作成
-- ==========================================
-- ユーザーテーブル
CREATE TABLE user (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    authority BOOLEAN NOT NULL
);

-- マット仕様テーブル
CREATE TABLE mat_specification (
    mat_spec_id INT AUTO_INCREMENT PRIMARY KEY,
    load_capacity INT NOT NULL,
    measurement_unit INT NOT NULL
);

-- 在庫テーブル
CREATE TABLE inventory (
    stock_id INT AUTO_INCREMENT PRIMARY KEY,
    stock_name VARCHAR(100) NOT NULL,
    amount INT NOT NULL,
    border_line INT NOT NULL,
    weight DECIMAL(10,3) NOT NULL,
    container_weight DECIMAL(10,3) NOT NULL,
    img VARCHAR(255) NOT NULL
);

-- 所有マットテーブル
CREATE TABLE possess_mat (
    mat_id INT AUTO_INCREMENT PRIMARY KEY,
    serial_id CHAR(12) NOT NULL UNIQUE,
    stock_id INT, -- NULLを許容
    mat_spec_id INT NOT NULL,
    FOREIGN KEY (stock_id) REFERENCES inventory(stock_id),
    FOREIGN KEY (mat_spec_id) REFERENCES mat_specification(mat_spec_id)
);

-- 在庫履歴テーブル
CREATE TABLE inventory_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    stock_id INT NOT NULL,
    stock_name VARCHAR(100) NOT NULL,
    amount INT NOT NULL,
    update_date DATETIME NOT NULL,
    quantity INT NOT NULL,
    weight DECIMAL(10,3) NOT NULL,
    max_weight DECIMAL(10,3) NOT NULL,
    FOREIGN KEY (stock_id) REFERENCES inventory(stock_id)
);

-- 外部キー制約チェックを有効化に戻す
SET FOREIGN_KEY_CHECKS = 1;

-- ==========================================
-- 3. テストデータの挿入
-- ==========================================

-- 0. ユーザー (25件)
INSERT INTO user (user_id, user_name, password, authority) VALUES
(NULL, '佐藤 健太', 'sato_ken01', TRUE), (NULL, '鈴木 さくら', 'suzuki_s99', FALSE),
(NULL, '高橋 大輔', 'takahashi_d', TRUE), (NULL, '田中 美咲', 'tanaka1234', FALSE),
(NULL, '伊藤 翔太', 'ito_shota5', TRUE), (NULL, '渡辺 結衣', 'watanabe_y', FALSE),
(NULL, '山本 浩二', 'yamamoto55', TRUE), (NULL, '中村 葵', 'nakamura08', FALSE),
(NULL, '小林 拓海', 'koba_taku', TRUE), (NULL, '加藤 陽菜', 'kato_hina', FALSE),
(NULL, '吉田 陸', 'yoshida_r', TRUE), (NULL, '山田 花子', 'yamada875', FALSE),
(NULL, '佐々木 蓮', 'sasaki_ren', TRUE), (NULL, '山口 凛', 'yamaguchi_r', FALSE),
(NULL, '松本 颯太', 'matsu_sota', TRUE), (NULL, '井上 結愛', 'inoue_yua', FALSE),
(NULL, '木村 樹', 'kimura_i', TRUE), (NULL, '林 さな', 'hayashi_s', FALSE),
(NULL, '斎藤 悠真', 'saito_yuma', TRUE), (NULL, '清水 美海', 'shimizu_m', FALSE),
(NULL, '山崎 蒼空', 'yamazaki_s', TRUE), (NULL, '森 あかり', 'mori_akari', FALSE),
(NULL, '池田 涼平', 'ikeda_ryo', TRUE), (NULL, '橋本 真由', 'hashimoto_m', FALSE),
(NULL, '阿部 健太郎', 'abe_ken33', TRUE);

-- 1. マット仕様 (4件)
INSERT INTO mat_specification (mat_spec_id, load_capacity, measurement_unit) VALUES
(1, 1000, 1), (2, 5000, 10), (3, 10000, 50), (4, 20000, 100);

-- 2. 在庫 (30件)
INSERT INTO inventory (stock_id, stock_name, amount, border_line, weight, container_weight, img) VALUES
(1, 'M2×10 なべ小ねじ', 10, 1000, 0.350, 0.150, 'screw_m2.png'),
(2, 'M3×15 六角穴付きボルト', 15, 500, 1.200, 0.200, 'bolt_m3.png'),
(3, 'M4 六角ナット', 5, 1000, 0.800, 0.100, 'nut_m4.png'),
(4, 'SUS304 平ワッシャー M4用', 8, 2000, 0.250, 0.120, 'washer_m4.png'),
(5, '深溝玉軸受 6200ZZ', 350, 50, 32.000, 1.500, 'bearing_6200.png'),
(6, 'リニアガイド HSR15', 2500, 20, 450.000, 2.100, 'linear_guide.png'),
(7, 'アルミフレーム 2020 (1000mm)', 800, 30, 480.000, 0.800, 'alum_2020.png'),
(8, 'アルミフレーム 4040 (1000mm)', 1800, 15, 1600.000, 1.200, 'alum_4040.png'),
(9, 'L字ブラケット 2020用', 120, 100, 15.500, 0.500, 'bracket_20.png'),
(10, 'Tナット M5', 30, 500, 3.100, 0.300, 'tnut_m5.png'),
(11, 'ステッピングモーター NEMA17', 1500, 25, 280.000, 0.600, 'motor_nema17.png'),
(12, 'サーボモーター SG90', 400, 50, 9.000, 0.250, 'servo_sg90.png'),
(13, 'タイミングベルト GT2 幅6mm', 200, 10, 12.000, 0.100, 'belt_gt2.png'),
(14, 'タイミングプーリー 20歯', 350, 40, 6.500, 0.150, 'pulley_20t.png'),
(15, 'カップリング 5×8mm', 600, 30, 18.000, 0.400, 'coupling.png'),
(16, 'Oリング P-10', 40, 200, 0.150, 0.050, 'oring_p10.png'),
(17, '結束バンド 100mm 白', 5, 50, 0.300, 0.100, 'cable_tie.png'),
(18, '熱収縮チューブ Φ3.0', 80, 20, 2.500, 0.200, 'tube_3.png'),
(19, 'プリント基板 ユニバーサル', 250, 40, 25.000, 0.350, 'pcb_uni.png'),
(20, '抵抗器 1kΩ 1/4W', 1, 1000, 0.200, 0.080, 'resistor_1k.png'),
(21, '電解コンデンサ 100μF', 20, 500, 1.500, 0.120, 'capacitor_100.png'),
(22, 'LED 5mm 赤', 15, 300, 0.300, 0.050, 'led_red.png'),
(23, 'トグルスイッチ 2極', 180, 50, 12.000, 0.300, 'switch_toggle.png'),
(24, 'リミットスイッチ', 320, 80, 8.500, 0.250, 'switch_limit.png'),
(25, 'VVFケーブル 1.6mm 2心', 1200, 5, 115.000, 1.800, 'cable_vvf.png'),
(26, '圧着端子 R2-4', 12, 400, 0.700, 0.200, 'terminal_r2.png'),
(27, '絶縁テープ 黒', 150, 30, 45.000, 0.150, 'tape_insulation.png'),
(28, 'シリコングリス', 850, 15, 20.000, 0.400, 'grease.png'),
(29, 'ねじロック剤', 600, 10, 50.000, 0.300, 'threadlocker.png'),
(30, 'ウレタンゴムシート 3mm', 950, 25, 320.000, 2.500, 'rubber_sheet.png');

-- 3. 所有マット (30件)
INSERT INTO possess_mat (mat_id, serial_id, stock_id, mat_spec_id) VALUES
(1, 'W93847102934', 1, 1), (2, 'W18475920384', 2, 2), (3, 'W82736450192', 3, 1),
(4, 'W39485710293', 4, 3), (5, 'W58291037465', 5, 1), (6, 'W10394857621', NULL, 1), 
(7, 'W74839201847', 7, 2), (8, 'W29384756102', 8, 1), (9, 'W85746392018', 9, 1),
(10, 'W19283746501', 10, 1), (11, 'W47586920193', NULL, 1), (12, 'W20394857162', 12, 2), 
(13, 'W84756102938', 13, 1), (14, 'W39485761029', 14, 2), (15, 'W58473629102', 15, 1), 
(16, 'W10293847561', 16, 1), (17, 'W94857102938', NULL, 1), (18, 'W28374650192', 18, 1), 
(19, 'W57483920183', 19, 1), (20, 'W19283746590', 20, 1), (21, 'W48571920384', 21, 2), 
(22, 'W37465019283', NULL, 2), (23, 'W58694039281', 23, 3), (24, 'W29384756109', 24, 2), 
(25, 'W84756192038', 25, 1), (26, 'W10293847569', NULL, 1), (27, 'W39485710294', 27, 1), 
(28, 'W58473629103', 28, 1), (29, 'W93847102935', 29, 1), (30, 'W18475920385', NULL, 1);

-- 4. 在庫履歴 (116640件: 27種類 × 各年2160時間(3ヶ月) × 2年分)
INSERT INTO inventory_history (stock_id, stock_name, amount, update_date, quantity, weight, max_weight)
SELECT
    base.stock_id,
    base.stock_name,
    base.amount,
    base.update_date,
    base.quantity,
    base.weight,
    (base.quantity * base.weight) AS max_weight
FROM (
    SELECT
        i.stock_id,
        i.stock_name,
        i.amount,
        -- 各年の基準日から1時間ずつ加算
        DATE_ADD(y.start_date, INTERVAL h.hour_offset HOUR) AS update_date,
        -- 個数を計算: 商品ごとに最大数(40〜79)と周期(72〜191時間)を変化させる
        FLOOR(
            (40 + (i.stock_id * 7) % 40) -- 各商品の最大個数
            -
            (
                ((h.hour_offset + i.stock_id * 23) % (72 + (i.stock_id * 13) % 120)) -- 前回の補充からの経過時間
                *
                (((40 + (i.stock_id * 7) % 40) - 10) / (72 + (i.stock_id * 13) % 120)) -- 1時間あたりの減少量
            )
        ) + FLOOR(RAND() * 4) AS quantity, -- 若干のノイズ(0〜3)を追加
        i.weight
    FROM (
        -- 0から2159までの連番（2160時間 = 90日分）を生成
        SELECT a.i + b.i * 10 + c.i * 100 + d.i * 1000 AS hour_offset
        FROM 
            (SELECT 0 AS i UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) a
            CROSS JOIN (SELECT 0 AS i UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) b
            CROSS JOIN (SELECT 0 AS i UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) c
            CROSS JOIN (SELECT 0 AS i UNION ALL SELECT 1 UNION ALL SELECT 2) d
        WHERE a.i + b.i * 10 + c.i * 100 + d.i * 1000 < 2160
    ) h
    -- 基準日を前年と今年の2つ用意する
    CROSS JOIN (
        SELECT '2022-10-01 00:00:00' AS start_date
        UNION ALL
        SELECT '2023-10-01 00:00:00' AS start_date
    ) y
    -- 28, 29, 30 の3種類を除外した27種類に対してクロスジョイン
    CROSS JOIN (
        SELECT * FROM inventory WHERE stock_id <= 27
    ) i
) base
ORDER BY base.update_date, base.stock_id;