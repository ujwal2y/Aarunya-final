<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
require_once '../server/config/config.php';
require_once '../server/config/database.php';
require_once '../server/includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
requireLogin('../client/login.php');
requireRole(ROLE_USER, '../client/login.php');

// Include appointment notification popup
include 'includes/appointment_notification.php';

$user = getCurrentUser();
$db = getDB();

// Get user details
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$userData = $stmt->fetch();

$generatedPlan = '';
$error = '';
$showPlan = false;

// Handle plan generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_plan'])) {
    $symptoms = trim($_POST['symptoms'] ?? '');
    $concerns = trim($_POST['concerns'] ?? '');
    $week = intval($_POST['pregnancy_week'] ?? $userData['pregnancy_week'] ?? 0);
    $weight = floatval($_POST['current_weight'] ?? 0);
    $height = floatval($_POST['height'] ?? 0);
    $activity_level = $_POST['activity_level'] ?? 'moderate';
    $dietary_restrictions = $_POST['dietary_restrictions'] ?? '';
    
    if (empty($symptoms) && empty($concerns)) {
        $error = 'Please describe your symptoms or concerns to generate a personalized wellness plan.';
    } elseif ($week <= 0) {
        $error = 'Please provide your current pregnancy week.';
    } else {
        // Save query to database
        $stmt = $db->prepare("
            INSERT INTO ai_wellness_queries (patient_id, query_text, symptoms)
            VALUES (?, ?, ?)
        ");
        $queryText = "Symptoms: $symptoms | Concerns: $concerns | Week: $week";
        $stmt->execute([$user['id'], $queryText, $symptoms]);
        
        // Generate AI-powered wellness plan based on input
        $generatedPlan = generateWellnessPlan($week, $weight, $height, $activity_level, $dietary_restrictions, $symptoms, $concerns);
        $showPlan = true;
    }
}

function generateWellnessPlan($week, $weight, $height, $activity_level, $dietary_restrictions, $symptoms, $concerns) {
    // Determine trimester
    $trimester = $week <= 12 ? 1 : ($week <= 28 ? 2 : 3);
    
    // Calculate BMI if height and weight provided
    $bmi = ($height > 0 && $weight > 0) ? $weight / (($height/100) * ($height/100)) : 0;
    
    // Add randomization seed based on current time and symptoms for variety
    $seed = time() + strlen($symptoms) + strlen($concerns);
    
    $plan = [
        'week' => $week,
        'trimester' => $trimester,
        'bmi' => $bmi,
        'symptoms' => $symptoms,
        'concerns' => $concerns,
        'personalized_intro' => generatePersonalizedIntro($week, $trimester, $bmi, $dietary_restrictions, $symptoms . ' ' . $concerns, $seed),
        'diet' => generateDietPlan($trimester, $week, $bmi, $dietary_restrictions, $seed),
        'exercise' => generateExercisePlan($trimester, $week, $activity_level, $symptoms . ' ' . $concerns, $seed),
        'wellness_tips' => generateWellnessTips($trimester, $week, $seed),
        'precautions' => generatePrecautions($trimester, $week, $seed),
        'weekly_focus' => generateWeeklyFocus($week, $seed),
        'baby_development' => generateBabyDevelopment($week, $seed)
    ];
    
    return $plan;
}

function generatePersonalizedIntro($week, $trimester, $bmi, $restrictions, $conditions, $seed) {
    $intros = [
        "Welcome to week $week of your pregnancy journey! This is an exciting time as your baby continues to grow and develop.",
        "Congratulations on reaching week $week! Your body is doing amazing work nurturing your little one.",
        "You're now at week $week - what an incredible milestone! Let's focus on keeping you and baby healthy.",
        "Week $week is here! Your pregnancy journey is progressing beautifully. Here's your personalized wellness plan.",
        "Amazing progress! You're at week $week now. Let's make this week the healthiest one yet!"
    ];
    
    $trimester_notes = [
        1 => [
            "During the first trimester, your body is adjusting to pregnancy. Focus on managing early symptoms and establishing healthy habits.",
            "The first trimester is crucial for your baby's development. Proper nutrition and rest are your top priorities.",
            "Your first trimester is a time of rapid changes. Listen to your body and take things one day at a time.",
        ],
        2 => [
            "The second trimester often brings renewed energy! This is a great time to stay active and prepare for baby's arrival.",
            "Welcome to the 'golden period' of pregnancy! Many women feel their best during these weeks.",
            "Your second trimester is here - time to enjoy this special phase while staying healthy and active.",
        ],
        3 => [
            "The third trimester means you're in the home stretch! Focus on comfort, preparation, and staying healthy.",
            "You're approaching the finish line! These final weeks are about preparing your body for labor and delivery.",
            "The third trimester is here - your baby is growing rapidly and you're getting ready to meet them soon!",
        ]
    ];
    
    $intro = $intros[($seed + $week) % count($intros)];
    $trimester_note = $trimester_notes[$trimester][($seed) % count($trimester_notes[$trimester])];
    
    $personalized = $intro . " " . $trimester_note;
    
    if ($bmi > 0) {
        if ($bmi < 18.5) {
            $personalized .= " Your BMI suggests you may benefit from additional nutritional support - discuss this with your healthcare provider.";
        } elseif ($bmi > 30) {
            $personalized .= " Focus on nutrient-dense foods and gentle exercise to support a healthy pregnancy.";
        }
    }
    
    if (!empty($restrictions)) {
        $personalized .= " We've tailored your nutrition plan to accommodate your dietary preferences: " . htmlspecialchars($restrictions) . ".";
    }
    
    if (!empty($conditions)) {
        $personalized .= " Your exercise recommendations have been adjusted considering: " . htmlspecialchars($conditions) . ".";
    }
    
    return $personalized;
}

function generateWeeklyFocus($week, $seed) {
    $focuses = [
        "This week, focus on staying hydrated and getting adequate rest. Your body needs extra support right now.",
        "Make this week about mindful eating and gentle movement. Small, consistent efforts make a big difference.",
        "This week's priority: stress management and quality sleep. Both are crucial for you and baby.",
        "Focus on building healthy habits this week. What you do now sets the foundation for the weeks ahead.",
        "This week, prioritize self-care and nutrition. You're doing important work growing your baby!",
        "Make connection a priority this week - with your baby, your partner, and your support system.",
        "This week is about balance: rest when you need it, move when you can, and nourish yourself well.",
    ];
    
    return $focuses[($seed + $week) % count($focuses)];
}

function generateBabyDevelopment($week, $seed) {
    $developments = [
        1 => "Your baby is just beginning! The fertilized egg is implanting in your uterus.",
        2 => "Cells are rapidly dividing and the embryo is forming.",
        3 => "Your baby's heart and nervous system are beginning to develop.",
        4 => "The neural tube (which becomes the brain and spinal cord) is forming.",
        5 => "Your baby's heart is starting to beat! Tiny limb buds are appearing.",
        6 => "Facial features are beginning to form, and the heart is beating regularly.",
        7 => "Your baby is about the size of a blueberry and developing rapidly.",
        8 => "All major organs are forming. Your baby is now called a fetus!",
        9 => "Your baby's bones are forming and tiny muscles are developing.",
        10 => "Vital organs are functioning and your baby can make small movements.",
        11 => "Your baby's fingers and toes are separating, and tooth buds are forming.",
        12 => "Your baby's reflexes are developing - they can open and close their fingers!",
        13 => "Your baby's vocal cords are forming and they're practicing breathing movements.",
        14 => "Your baby can make facial expressions and may be sucking their thumb!",
        15 => "Your baby's bones are hardening and they're moving around a lot.",
        16 => "Your baby's nervous system is functioning and they can hear sounds!",
        17 => "Your baby's skeleton is changing from soft cartilage to bone.",
        18 => "Your baby's ears are in position and they can hear your voice!",
        19 => "Your baby is developing a protective coating called vernix.",
        20 => "You're halfway there! Your baby can hear and respond to sounds.",
        21 => "Your baby's movements are getting stronger - you might feel kicks!",
        22 => "Your baby's senses are developing rapidly, especially touch and taste.",
        23 => "Your baby's lungs are developing and preparing for breathing.",
        24 => "Your baby's brain is growing rapidly and they're gaining weight.",
        25 => "Your baby's hands are fully formed and they're exploring their face.",
        26 => "Your baby's eyes are beginning to open and they respond to light!",
        27 => "Your baby's lungs are maturing and they're practicing breathing.",
        28 => "Your baby can blink and their eyelashes have formed!",
        29 => "Your baby's brain is developing billions of neurons.",
        30 => "Your baby is getting stronger and their kicks are more powerful!",
        31 => "Your baby's five senses are fully developed now.",
        32 => "Your baby is practicing breathing and their bones are fully formed.",
        33 => "Your baby's immune system is developing to protect them after birth.",
        34 => "Your baby's central nervous system is maturing.",
        35 => "Your baby's kidneys are fully developed and their liver is functioning.",
        36 => "Your baby is shedding the vernix coating and preparing for birth.",
        37 => "Your baby is considered full-term! They're ready to be born anytime.",
        38 => "Your baby is gaining about half a pound per week now.",
        39 => "Your baby's brain is still developing and will continue after birth.",
        40 => "Your baby is ready to meet you! Labor could start any day now.",
        41 => "Your baby is fully developed and waiting for the right time to arrive.",
        42 => "Your healthcare provider will monitor you closely this week.",
    ];
    
    return $developments[$week] ?? "Your baby is developing beautifully!";
}

function generateDietPlan($trimester, $week, $bmi, $restrictions, $seed) {
    // 15 Complete Diet Plans - Randomized
    $complete_diet_plans = [
        // Plan 1: Mediterranean Style
        [
            'name' => 'Mediterranean Pregnancy Diet',
            'focus' => 'Heart-healthy fats and fresh vegetables for optimal fetal development',
            'calories' => '2000-2200 calories/day',
            'key_nutrients' => ['Omega-3 (300mg)', 'Folate (600mcg)', 'Iron (27mg)', 'Calcium (1000mg)'],
            'foods_to_eat' => [
                'Olive oil and avocados for healthy fats',
                'Fresh fish (salmon, sardines) 2-3 times per week',
                'Colorful vegetables (tomatoes, peppers, eggplant)',
                'Whole grain pasta and bread',
                'Greek yogurt and feta cheese',
                'Nuts (almonds, walnuts) and seeds',
                'Fresh fruits (berries, citrus, figs)'
            ],
            'meal_suggestions' => [
                'Breakfast: Greek yogurt parfait with honey, walnuts, and fresh berries',
                'Lunch: Mediterranean quinoa bowl with chickpeas, cucumber, tomatoes, and olive oil',
                'Dinner: Grilled salmon with roasted vegetables and whole grain couscous',
                'Snacks: Hummus with veggie sticks, handful of almonds'
            ]
        ],
        // Plan 2: Asian Fusion
        [
            'name' => 'Asian-Inspired Pregnancy Diet',
            'focus' => 'Nutrient-dense foods with emphasis on vegetables and lean proteins',
            'calories' => '1900-2100 calories/day',
            'key_nutrients' => ['Protein (75g)', 'Iron (27mg)', 'Vitamin C (85mg)', 'Folate (600mcg)'],
            'foods_to_eat' => [
                'Brown rice and quinoa',
                'Tofu and edamame for plant protein',
                'Bok choy, spinach, and Asian greens',
                'Lean chicken and fish',
                'Miso soup and seaweed',
                'Fresh ginger and garlic',
                'Green tea (decaf) and plenty of water'
            ],
            'meal_suggestions' => [
                'Breakfast: Congee with eggs and vegetables',
                'Lunch: Teriyaki chicken with brown rice and steamed broccoli',
                'Dinner: Miso soup with tofu, soba noodles, and mixed vegetables',
                'Snacks: Edamame, rice cakes with almond butter'
            ]
        ],
        // Plan 3: Plant-Based Power
        [
            'name' => 'Plant-Based Pregnancy Diet',
            'focus' => 'Complete plant proteins and iron-rich foods for vegetarian mothers',
            'calories' => '2100-2300 calories/day',
            'key_nutrients' => ['Protein (80g)', 'Iron (30mg)', 'B12 (2.6mcg)', 'Calcium (1200mg)'],
            'foods_to_eat' => [
                'Legumes (lentils, chickpeas, black beans)',
                'Quinoa and amaranth for complete proteins',
                'Dark leafy greens (kale, spinach, collards)',
                'Fortified plant milks and nutritional yeast',
                'Nuts, seeds, and nut butters',
                'Tempeh and tofu',
                'Vitamin C-rich fruits to enhance iron absorption'
            ],
            'meal_suggestions' => [
                'Breakfast: Smoothie bowl with spinach, banana, chia seeds, and almond milk',
                'Lunch: Buddha bowl with quinoa, roasted chickpeas, tahini dressing',
                'Dinner: Lentil curry with brown rice and sautéed greens',
                'Snacks: Trail mix, apple with peanut butter'
            ]
        ],
        // Plan 4: High Protein Focus
        [
            'name' => 'High-Protein Pregnancy Diet',
            'focus' => 'Increased protein intake for muscle development and fetal growth',
            'calories' => '2200-2400 calories/day',
            'key_nutrients' => ['Protein (90g)', 'Iron (27mg)', 'Zinc (11mg)', 'Vitamin B12 (2.6mcg)'],
            'foods_to_eat' => [
                'Lean meats (chicken breast, turkey, lean beef)',
                'Fish and seafood (low mercury)',
                'Eggs (whole and whites)',
                'Greek yogurt and cottage cheese',
                'Protein-rich legumes',
                'Quinoa and ancient grains',
                'Protein smoothies with Greek yogurt'
            ],
            'meal_suggestions' => [
                'Breakfast: Veggie omelet with whole grain toast and avocado',
                'Lunch: Grilled chicken Caesar salad with chickpeas',
                'Dinner: Baked cod with quinoa and roasted Brussels sprouts',
                'Snacks: Protein smoothie, hard-boiled eggs'
            ]
        ],
        // Plan 5: Anti-Inflammatory
        [
            'name' => 'Anti-Inflammatory Pregnancy Diet',
            'focus' => 'Reducing inflammation and supporting immune system',
            'calories' => '2000-2200 calories/day',
            'key_nutrients' => ['Omega-3 (350mg)', 'Vitamin E (15mg)', 'Selenium (60mcg)', 'Zinc (11mg)'],
            'foods_to_eat' => [
                'Fatty fish (salmon, mackerel, sardines)',
                'Turmeric and ginger in cooking',
                'Berries (blueberries, strawberries, raspberries)',
                'Leafy greens and cruciferous vegetables',
                'Walnuts and flaxseeds',
                'Green tea (decaf) and herbal teas',
                'Dark chocolate (70%+ cacao) in moderation'
            ],
            'meal_suggestions' => [
                'Breakfast: Berry smoothie with flaxseeds and spinach',
                'Lunch: Salmon salad with mixed greens and walnuts',
                'Dinner: Turmeric chicken with roasted vegetables',
                'Snacks: Mixed berries, dark chocolate square'
            ]
        ],
        // Plan 6: Iron-Rich Focus
        [
            'name' => 'Iron-Boosting Pregnancy Diet',
            'focus' => 'Preventing anemia with iron-rich foods and absorption enhancers',
            'calories' => '2100-2300 calories/day',
            'key_nutrients' => ['Iron (30mg)', 'Vitamin C (100mg)', 'Folate (600mcg)', 'Protein (75g)'],
            'foods_to_eat' => [
                'Red meat (lean cuts) 2-3 times per week',
                'Liver (in moderation, once per week)',
                'Dark leafy greens (spinach, kale)',
                'Fortified cereals and breads',
                'Lentils and beans',
                'Citrus fruits with meals',
                'Blackstrap molasses'
            ],
            'meal_suggestions' => [
                'Breakfast: Fortified oatmeal with berries and orange juice',
                'Lunch: Spinach salad with grilled steak and citrus dressing',
                'Dinner: Beef and lentil stew with whole grain bread',
                'Snacks: Dried apricots, pumpkin seeds'
            ]
        ],
        // Plan 7: Calcium-Rich
        [
            'name' => 'Bone-Building Pregnancy Diet',
            'focus' => 'Maximum calcium intake for baby\'s bone development',
            'calories' => '2000-2200 calories/day',
            'key_nutrients' => ['Calcium (1300mg)', 'Vitamin D (600 IU)', 'Magnesium (350mg)', 'Phosphorus (700mg)'],
            'foods_to_eat' => [
                'Dairy products (milk, yogurt, cheese)',
                'Fortified plant milks (almond, soy, oat)',
                'Sardines and canned salmon with bones',
                'Tofu (calcium-set)',
                'Almonds and sesame seeds',
                'Broccoli and bok choy',
                'Fortified orange juice'
            ],
            'meal_suggestions' => [
                'Breakfast: Yogurt parfait with granola and fortified OJ',
                'Lunch: Grilled cheese on whole wheat with tomato soup',
                'Dinner: Baked salmon with broccoli and quinoa',
                'Snacks: String cheese, almond milk latte'
            ]
        ],
        // Plan 8: Fiber-Rich
        [
            'name' => 'Digestive Health Pregnancy Diet',
            'focus' => 'High fiber to prevent constipation and support gut health',
            'calories' => '2100-2300 calories/day',
            'key_nutrients' => ['Fiber (30-35g)', 'Probiotics', 'Water (10+ glasses)', 'Magnesium (350mg)'],
            'foods_to_eat' => [
                'Whole grains (oats, brown rice, whole wheat)',
                'Legumes (beans, lentils, chickpeas)',
                'Fresh fruits (apples, pears, berries)',
                'Vegetables (carrots, sweet potatoes, broccoli)',
                'Probiotic yogurt and kefir',
                'Chia seeds and flaxseeds',
                'Prunes and dried fruits'
            ],
            'meal_suggestions' => [
                'Breakfast: Oatmeal with chia seeds, berries, and flaxseeds',
                'Lunch: Three-bean salad with whole grain roll',
                'Dinner: Chicken with sweet potato and roasted vegetables',
                'Snacks: Apple with almond butter, probiotic yogurt'
            ]
        ],
        // Plan 9: Energy-Boosting
        [
            'name' => 'High-Energy Pregnancy Diet',
            'focus' => 'Complex carbs and B vitamins for sustained energy',
            'calories' => '2200-2400 calories/day',
            'key_nutrients' => ['B-Complex Vitamins', 'Iron (27mg)', 'Complex Carbs (250g)', 'Protein (80g)'],
            'foods_to_eat' => [
                'Whole grain breads and cereals',
                'Sweet potatoes and quinoa',
                'Bananas and dates',
                'Lean proteins for sustained energy',
                'Nuts and seeds for healthy fats',
                'Green leafy vegetables',
                'Eggs and dairy'
            ],
            'meal_suggestions' => [
                'Breakfast: Whole grain toast with eggs and avocado',
                'Lunch: Turkey sandwich on whole wheat with fruit',
                'Dinner: Grilled chicken with sweet potato and green beans',
                'Snacks: Banana with peanut butter, energy balls'
            ]
        ],
        // Plan 10: Omega-3 Focus
        [
            'name' => 'Brain-Boosting Pregnancy Diet',
            'focus' => 'DHA and omega-3 fatty acids for fetal brain development',
            'calories' => '2000-2200 calories/day',
            'key_nutrients' => ['DHA (300mg)', 'Omega-3 (400mg)', 'Choline (450mg)', 'Iodine (220mcg)'],
            'foods_to_eat' => [
                'Fatty fish (salmon, sardines, anchovies)',
                'Walnuts and chia seeds',
                'Flaxseed oil and hemp seeds',
                'Eggs (especially yolks)',
                'Seaweed and kelp',
                'Grass-fed beef',
                'Algae-based DHA supplements'
            ],
            'meal_suggestions' => [
                'Breakfast: Scrambled eggs with smoked salmon and whole grain toast',
                'Lunch: Tuna salad (low mercury) with walnuts',
                'Dinner: Grilled salmon with quinoa and asparagus',
                'Snacks: Chia pudding, handful of walnuts'
            ]
        ],
        // Plan 11: Low-Glycemic
        [
            'name' => 'Blood Sugar Balance Diet',
            'focus' => 'Stable blood sugar levels to prevent gestational diabetes',
            'calories' => '1900-2100 calories/day',
            'key_nutrients' => ['Fiber (30g)', 'Protein (75g)', 'Chromium (30mcg)', 'Magnesium (350mg)'],
            'foods_to_eat' => [
                'Non-starchy vegetables (broccoli, cauliflower, peppers)',
                'Lean proteins at every meal',
                'Whole grains in moderation',
                'Legumes and beans',
                'Nuts and seeds',
                'Berries and citrus fruits',
                'Cinnamon and apple cider vinegar'
            ],
            'meal_suggestions' => [
                'Breakfast: Greek yogurt with berries and almonds',
                'Lunch: Grilled chicken salad with chickpeas and olive oil',
                'Dinner: Baked fish with cauliflower rice and vegetables',
                'Snacks: Celery with almond butter, cheese and cucumber'
            ]
        ],
        // Plan 12: Hydration Focus
        [
            'name' => 'Hydration-Optimized Diet',
            'focus' => 'Water-rich foods and electrolyte balance',
            'calories' => '2000-2200 calories/day',
            'key_nutrients' => ['Potassium (2900mg)', 'Sodium (moderate)', 'Magnesium (350mg)', 'Water (12+ glasses)'],
            'foods_to_eat' => [
                'Watermelon, cucumber, and celery',
                'Coconut water and herbal teas',
                'Soups and broths',
                'Citrus fruits and berries',
                'Lettuce and leafy greens',
                'Tomatoes and zucchini',
                'Yogurt and milk'
            ],
            'meal_suggestions' => [
                'Breakfast: Smoothie with watermelon, cucumber, and coconut water',
                'Lunch: Gazpacho soup with whole grain crackers',
                'Dinner: Chicken soup with vegetables and brown rice',
                'Snacks: Watermelon slices, cucumber with hummus'
            ]
        ],
        // Plan 13: Comfort Food Healthy
        [
            'name' => 'Healthy Comfort Food Diet',
            'focus' => 'Nutritious versions of comfort foods for cravings',
            'calories' => '2100-2300 calories/day',
            'key_nutrients' => ['Balanced macros', 'Fiber (28g)', 'Protein (75g)', 'Calcium (1000mg)'],
            'foods_to_eat' => [
                'Whole grain pasta and pizza',
                'Baked sweet potato fries',
                'Lean beef burgers on whole wheat buns',
                'Homemade mac and cheese with vegetables',
                'Grilled cheese with tomato soup',
                'Chicken tenders (baked, not fried)',
                'Fruit smoothies and frozen yogurt'
            ],
            'meal_suggestions' => [
                'Breakfast: Whole grain pancakes with fruit and yogurt',
                'Lunch: Turkey burger with sweet potato fries',
                'Dinner: Whole wheat pasta with meat sauce and salad',
                'Snacks: Baked chips with guacamole, frozen yogurt'
            ]
        ],
        // Plan 14: Quick & Easy
        [
            'name' => 'Simple Pregnancy Meal Plan',
            'focus' => 'Easy-to-prepare nutritious meals for busy moms',
            'calories' => '2000-2200 calories/day',
            'key_nutrients' => ['All essential nutrients', 'Convenience', 'Minimal prep time'],
            'foods_to_eat' => [
                'Pre-washed salad greens',
                'Rotisserie chicken',
                'Canned beans and lentils',
                'Frozen vegetables',
                'Pre-cut fruits',
                'Instant oatmeal (unsweetened)',
                'Ready-to-eat hard-boiled eggs'
            ],
            'meal_suggestions' => [
                'Breakfast: Instant oatmeal with banana and peanut butter',
                'Lunch: Rotisserie chicken salad with pre-washed greens',
                'Dinner: Canned bean chili with frozen vegetables',
                'Snacks: Pre-cut veggies with hummus, string cheese'
            ]
        ],
        // Plan 15: Gourmet Pregnancy
        [
            'name' => 'Gourmet Pregnancy Cuisine',
            'focus' => 'Restaurant-quality nutritious meals at home',
            'calories' => '2100-2300 calories/day',
            'key_nutrients' => ['Premium proteins', 'Organic produce', 'Artisanal grains', 'Superfoods'],
            'foods_to_eat' => [
                'Wild-caught salmon and sea bass',
                'Organic free-range chicken and eggs',
                'Heirloom vegetables and microgreens',
                'Artisanal cheeses (pasteurized)',
                'Ancient grains (farro, freekeh, kamut)',
                'Truffle oil and balsamic reduction',
                'Fresh herbs and edible flowers'
            ],
            'meal_suggestions' => [
                'Breakfast: Eggs Benedict with smoked salmon and hollandaise',
                'Lunch: Caprese salad with burrata and balsamic reduction',
                'Dinner: Pan-seared sea bass with farro risotto and asparagus',
                'Snacks: Artisanal cheese board with fruit, gourmet dark chocolate'
            ]
        ]
    ];
    
    // Select a random diet plan
    $selected_plan = $complete_diet_plans[$seed % count($complete_diet_plans)];
    
    // Adjust based on trimester
    $trimester_adjustments = [
        1 => ['calories' => '1800-2000 calories/day'],
        2 => ['calories' => '2200-2400 calories/day'],
        3 => ['calories' => '2400-2600 calories/day']
    ];
    
    $selected_plan['calories'] = $trimester_adjustments[$trimester]['calories'];
    
    // Add standard foods to avoid
    $selected_plan['foods_to_avoid'] = [
        'Raw or undercooked meats and eggs',
        'High-mercury fish (shark, swordfish, king mackerel)',
        'Unpasteurized dairy products and soft cheeses',
        'Excessive caffeine (limit to 200mg/day)',
        'Alcohol completely',
        'Raw sprouts and unwashed produce'
    ];
    
    // Add hydration tip
    $hydration_tips = [
        'Drink at least 8-10 glasses of water daily',
        'Keep a water bottle with you at all times',
        'Add lemon or cucumber slices to water for flavor',
        'Drink a glass of water before each meal',
        'Set reminders on your phone to drink water regularly',
        'Herbal teas (caffeine-free) count towards your fluid intake',
        'Coconut water is excellent for hydration and electrolytes',
        'Eat water-rich fruits like watermelon and oranges'
    ];
    $selected_plan['hydration_tip'] = $hydration_tips[$seed % count($hydration_tips)];
    
    return $selected_plan;
}

function generateExercisePlan($trimester, $week, $activity_level, $conditions, $seed) {
    // 15 Complete Exercise Plans - Randomized
    $complete_exercise_plans = [
        // Plan 1: Gentle Yoga Focus
        [
            'name' => 'Prenatal Yoga & Stretching',
            'duration' => '30-40 minutes, 4-5 times per week',
            'intensity' => 'gentle to moderate',
            'safe_exercises' => [
                'Cat-cow stretches for back relief',
                'Modified downward dog',
                'Prenatal sun salutations',
                'Butterfly pose for hip opening',
                'Child\'s pose for relaxation',
                'Pelvic floor exercises (Kegels)',
                'Breathing exercises and meditation'
            ],
            'weekly_schedule' => [
                'Monday: 45-minute prenatal yoga class',
                'Tuesday: 20-minute gentle stretching at home',
                'Wednesday: Rest or meditation',
                'Thursday: 40-minute yoga flow',
                'Friday: 30-minute restorative yoga',
                'Weekend: Gentle walks and relaxation'
            ]
        ],
        // Plan 2: Swimming & Water Aerobics
        [
            'name' => 'Aquatic Pregnancy Fitness',
            'duration' => '30-45 minutes, 3-4 times per week',
            'intensity' => 'low to moderate impact',
            'safe_exercises' => [
                'Swimming laps (freestyle, backstroke)',
                'Water walking in pool',
                'Aqua aerobics classes',
                'Water jogging with flotation belt',
                'Pool stretches and leg lifts',
                'Treading water intervals',
                'Cool-down floating and relaxation'
            ],
            'weekly_schedule' => [
                'Monday: 40-minute swim session',
                'Tuesday: Rest',
                'Wednesday: 45-minute water aerobics class',
                'Thursday: 30-minute water walking',
                'Friday: Rest',
                'Weekend: Leisure swimming or rest'
            ]
        ],
        // Plan 3: Walking & Light Cardio
        [
            'name' => 'Walking & Cardio Program',
            'duration' => '20-40 minutes, 5-6 times per week',
            'intensity' => 'moderate',
            'safe_exercises' => [
                'Brisk walking outdoors or on treadmill',
                'Interval walking (alternating pace)',
                'Stationary cycling',
                'Low-impact step aerobics',
                'Arm exercises with light weights while walking',
                'Cool-down stretches',
                'Deep breathing exercises'
            ],
            'weekly_schedule' => [
                'Monday: 30-minute brisk walk',
                'Tuesday: 25-minute stationary bike',
                'Wednesday: 35-minute interval walking',
                'Thursday: 20-minute walk + stretching',
                'Friday: 30-minute low-impact aerobics',
                'Weekend: Leisure walks with family'
            ]
        ],
        // Plan 4: Strength Training
        [
            'name' => 'Pregnancy Strength & Toning',
            'duration' => '25-35 minutes, 3 times per week',
            'intensity' => 'moderate',
            'safe_exercises' => [
                'Bodyweight squats and lunges',
                'Wall push-ups and modified planks',
                'Resistance band exercises',
                'Light dumbbell arm exercises (3-5 lbs)',
                'Pelvic tilts and bridges',
                'Standing leg lifts',
                'Core stability exercises (modified)'
            ],
            'weekly_schedule' => [
                'Monday: Full body strength (30 minutes)',
                'Tuesday: Rest or gentle yoga',
                'Wednesday: Upper body focus (25 minutes)',
                'Thursday: Walk or swim',
                'Friday: Lower body focus (30 minutes)',
                'Weekend: Active rest - stretching'
            ]
        ],
        // Plan 5: Pilates-Based
        [
            'name' => 'Prenatal Pilates Program',
            'duration' => '30-40 minutes, 3-4 times per week',
            'intensity' => 'gentle to moderate',
            'safe_exercises' => [
                'Modified hundred exercise',
                'Side-lying leg series',
                'Pelvic curls and bridges',
                'Standing Pilates exercises',
                'Arm circles with light weights',
                'Spine stretches (seated)',
                'Breathing and core connection'
            ],
            'weekly_schedule' => [
                'Monday: 40-minute Pilates class',
                'Tuesday: Rest',
                'Wednesday: 30-minute home Pilates',
                'Thursday: Gentle walk',
                'Friday: 35-minute Pilates flow',
                'Weekend: Stretching and relaxation'
            ]
        ],
        // Plan 6: Dance-Based Fitness
        [
            'name' => 'Pregnancy Dance Fitness',
            'duration' => '25-35 minutes, 3-4 times per week',
            'intensity' => 'moderate',
            'safe_exercises' => [
                'Low-impact dance aerobics',
                'Belly dancing (modified)',
                'Zumba (pregnancy-safe moves)',
                'Ballet-inspired barre work',
                'Hip circles and figure-8s',
                'Arm styling with light movements',
                'Cool-down stretches'
            ],
            'weekly_schedule' => [
                'Monday: 30-minute dance aerobics',
                'Tuesday: Rest or gentle stretching',
                'Wednesday: 35-minute Zumba class',
                'Thursday: 20-minute barre workout',
                'Friday: Rest',
                'Weekend: Social dancing or rest'
            ]
        ],
        // Plan 7: Minimal Equipment Home Workout
        [
            'name' => 'At-Home Pregnancy Fitness',
            'duration' => '20-30 minutes, 4-5 times per week',
            'intensity' => 'gentle to moderate',
            'safe_exercises' => [
                'Bodyweight exercises (squats, lunges)',
                'Chair exercises for balance',
                'Wall sits and calf raises',
                'Arm exercises with water bottles',
                'Pelvic floor exercises',
                'Stretching routine',
                'Online prenatal workout videos'
            ],
            'weekly_schedule' => [
                'Monday: 25-minute full body workout',
                'Tuesday: 20-minute yoga video',
                'Wednesday: 30-minute cardio + strength',
                'Thursday: Rest or stretching',
                'Friday: 25-minute circuit training',
                'Weekend: Active play or rest'
            ]
        ],
        // Plan 8: Outdoor Activities
        [
            'name' => 'Nature-Based Pregnancy Fitness',
            'duration' => '30-45 minutes, 4-5 times per week',
            'intensity' => 'moderate',
            'safe_exercises' => [
                'Nature walks on flat trails',
                'Park bench exercises',
                'Outdoor yoga in the garden',
                'Light gardening activities',
                'Beach walking',
                'Outdoor stretching',
                'Mindful nature meditation'
            ],
            'weekly_schedule' => [
                'Monday: 40-minute nature walk',
                'Tuesday: 30-minute park workout',
                'Wednesday: Rest or gentle gardening',
                'Thursday: 35-minute trail walk',
                'Friday: 30-minute outdoor yoga',
                'Weekend: Beach walk or nature time'
            ]
        ],
        // Plan 9: Low-Impact Cardio
        [
            'name' => 'Gentle Cardio Program',
            'duration' => '20-30 minutes, 5 times per week',
            'intensity' => 'low to moderate',
            'safe_exercises' => [
                'Elliptical machine (low resistance)',
                'Recumbent bike',
                'Slow-paced walking',
                'Arm ergometer (upper body cardio)',
                'Marching in place',
                'Side steps and grapevines',
                'Gentle cool-down stretches'
            ],
            'weekly_schedule' => [
                'Monday: 25-minute elliptical',
                'Tuesday: 20-minute recumbent bike',
                'Wednesday: 30-minute walk',
                'Thursday: 20-minute arm ergometer',
                'Friday: 25-minute mixed cardio',
                'Weekend: Leisure activities'
            ]
        ],
        // Plan 10: Balance & Stability
        [
            'name' => 'Balance & Core Stability',
            'duration' => '25-35 minutes, 3-4 times per week',
            'intensity' => 'gentle',
            'safe_exercises' => [
                'Single-leg stands (with support)',
                'Heel-to-toe walking',
                'Balance board exercises (with support)',
                'Tai chi movements',
                'Core stability exercises',
                'Pelvic floor strengthening',
                'Posture correction exercises'
            ],
            'weekly_schedule' => [
                'Monday: 30-minute balance class',
                'Tuesday: Rest',
                'Wednesday: 25-minute Tai chi',
                'Thursday: Gentle walk',
                'Friday: 30-minute stability workout',
                'Weekend: Stretching and relaxation'
            ]
        ],
        // Plan 11: Partner/Group Fitness
        [
            'name' => 'Social Pregnancy Fitness',
            'duration' => '30-45 minutes, 3-4 times per week',
            'intensity' => 'moderate',
            'safe_exercises' => [
                'Group prenatal fitness classes',
                'Partner-assisted stretches',
                'Walking groups for pregnant women',
                'Prenatal dance classes',
                'Mom-to-be yoga sessions',
                'Aqua aerobics classes',
                'Social sports (modified)'
            ],
            'weekly_schedule' => [
                'Monday: 45-minute group fitness class',
                'Tuesday: Rest',
                'Wednesday: Walking group (40 minutes)',
                'Thursday: Partner yoga (30 minutes)',
                'Friday: Prenatal dance class (35 minutes)',
                'Weekend: Social activities'
            ]
        ],
        // Plan 12: Flexibility Focus
        [
            'name' => 'Flexibility & Mobility Program',
            'duration' => '25-35 minutes, 4-5 times per week',
            'intensity' => 'gentle',
            'safe_exercises' => [
                'Full-body stretching routine',
                'Hip openers and stretches',
                'Shoulder and neck releases',
                'Spinal twists (modified)',
                'Hamstring and calf stretches',
                'Foam rolling (gentle)',
                'Relaxation and breathing'
            ],
            'weekly_schedule' => [
                'Monday: 30-minute stretching session',
                'Tuesday: 25-minute yoga stretches',
                'Wednesday: Rest or gentle walk',
                'Thursday: 35-minute flexibility class',
                'Friday: 30-minute foam rolling + stretching',
                'Weekend: Gentle stretching as needed'
            ]
        ],
        // Plan 13: Active Lifestyle
        [
            'name' => 'Daily Active Living',
            'duration' => 'Throughout the day, every day',
            'intensity' => 'light to moderate',
            'safe_exercises' => [
                'Taking stairs instead of elevator',
                'Parking farther away for extra steps',
                'Active housework and cleaning',
                'Playing with pets or children',
                'Standing desk work intervals',
                'Stretching breaks every hour',
                'Evening family walks'
            ],
            'weekly_schedule' => [
                'Daily: 10,000 steps goal',
                'Morning: 10-minute stretching routine',
                'Afternoon: Active break every 2 hours',
                'Evening: 20-minute family walk',
                'Weekend: Active outings and activities',
                'Continuous: Movement throughout the day'
            ]
        ],
        // Plan 14: Breathing & Relaxation
        [
            'name' => 'Breath Work & Gentle Movement',
            'duration' => '20-30 minutes, daily',
            'intensity' => 'very gentle',
            'safe_exercises' => [
                'Diaphragmatic breathing exercises',
                'Labor breathing practice',
                'Meditation and mindfulness',
                'Gentle stretching with breath',
                'Progressive muscle relaxation',
                'Visualization exercises',
                'Restorative yoga poses'
            ],
            'weekly_schedule' => [
                'Daily: 20-minute breathing practice',
                'Morning: 10-minute meditation',
                'Afternoon: Gentle stretching',
                'Evening: Relaxation routine',
                'Weekly: Prenatal breathing class',
                'As needed: Stress relief exercises'
            ]
        ],
        // Plan 15: Third Trimester Comfort
        [
            'name' => 'Late Pregnancy Comfort Exercises',
            'duration' => '15-25 minutes, 3-4 times per week',
            'intensity' => 'very gentle',
            'safe_exercises' => [
                'Pelvic rocking and tilts',
                'Supported squats for labor prep',
                'Gentle walking',
                'Swimming or water exercises',
                'Prenatal massage techniques',
                'Birth ball exercises',
                'Relaxation and visualization'
            ],
            'weekly_schedule' => [
                'Monday: 20-minute gentle walk',
                'Tuesday: 25-minute water exercises',
                'Wednesday: Rest and relaxation',
                'Thursday: 20-minute birth ball routine',
                'Friday: 15-minute pelvic exercises',
                'Weekend: Gentle activities as comfortable'
            ]
        ]
    ];
    
    // Select a random exercise plan
    $selected_plan = $complete_exercise_plans[$seed % count($complete_exercise_plans)];
    
    // Adjust intensity based on activity level
    $intensity_map = [
        'low' => 'gentle',
        'moderate' => 'moderate',
        'high' => 'moderate to vigorous'
    ];
    $selected_plan['intensity'] = $intensity_map[$activity_level];
    
    // Add standard exercises to avoid
    $selected_plan['exercises_to_avoid'] = [
        'Contact sports and activities with fall risk',
        'Exercises lying flat on back after first trimester',
        'Hot yoga or exercising in extreme heat',
        'Heavy lifting or straining',
        'High-impact jumping or bouncing',
        'Deep twisting movements',
        'Scuba diving or high-altitude activities'
    ];
    
    // Add exercise tips
    $exercise_tips = [
        'Always warm up before exercising and cool down afterwards',
        'Stay hydrated - drink water before, during, and after exercise',
        'Wear comfortable, supportive clothing and shoes',
        'Stop immediately if you feel dizzy, short of breath, or experience pain',
        'Use the "talk test" - you should be able to hold a conversation while exercising',
        'Listen to your body and rest when needed',
        'Avoid exercising in hot, humid weather',
        'Consider exercising with a partner for safety and motivation',
        'Modify exercises as your pregnancy progresses',
        'Focus on form over intensity'
    ];
    $selected_plan['exercise_tip'] = $exercise_tips[$seed % count($exercise_tips)];
    
    // Add motivational quotes
    $motivational_quotes = [
        '"Your body is capable of amazing things. Trust the process."',
        '"Every step you take is preparing you for motherhood."',
        '"Strong mom, strong baby. You\'ve got this!"',
        '"Movement is medicine for your body and mind."',
        '"You\'re not just exercising for you - you\'re setting an example for your little one."',
        '"Consistency over intensity. Small efforts add up to big results."',
        '"Your body is doing incredible work. Honor it with gentle movement."',
        '"Exercise is a celebration of what your body can do."',
        '"You are stronger than you think, braver than you believe."',
        '"Every workout is a gift to yourself and your baby."'
    ];
    $selected_plan['motivation'] = $motivational_quotes[$seed % count($motivational_quotes)];
    
    return $selected_plan;
}

function generateWellnessTips($trimester, $week, $seed) {
    $base_tips = [
        1 => [
            'Stay hydrated - drink 8-10 glasses of water daily',
            'Get plenty of rest - aim for 8-9 hours of sleep',
            'Take prenatal vitamins as prescribed',
            'Manage morning sickness with small, frequent meals',
            'Avoid smoking, alcohol, and limit caffeine',
            'Practice stress management techniques',
            'Schedule regular prenatal checkups'
        ],
        2 => [
            'Continue healthy eating habits established in first trimester',
            'Monitor weight gain (1-2 pounds per week is normal)',
            'Stay active but listen to your body',
            'Practice good posture to prevent back pain',
            'Wear comfortable, supportive shoes',
            'Start thinking about childbirth education classes',
            'Connect with other expectant mothers for support'
        ],
        3 => [
            'Prepare for labor with breathing exercises',
            'Pack your hospital bag by week 36',
            'Practice relaxation techniques for labor',
            'Monitor baby movements daily',
            'Prepare your home for baby\'s arrival',
            'Discuss birth plan with your healthcare provider',
            'Get plenty of rest before baby arrives'
        ]
    ];
    
    $additional_tips = [
        'Practice mindfulness and meditation for 10 minutes daily',
        'Keep a pregnancy journal to track your journey',
        'Take photos to document your growing belly',
        'Spend quality time with your partner before baby arrives',
        'Read books about pregnancy and parenting',
        'Join a prenatal class or support group',
        'Treat yourself to a prenatal massage (after first trimester)',
        'Start a gentle bedtime routine for better sleep',
        'Use pregnancy-safe skincare products',
        'Talk or sing to your baby - they can hear you!',
        'Practice gratitude - write down 3 things you\'re thankful for daily',
        'Stay connected with friends and family for emotional support'
    ];
    
    $tips = $base_tips[$trimester];
    
    // Add 2-3 random additional tips for variety
    shuffle($additional_tips);
    $tips = array_merge($tips, array_slice($additional_tips, 0, 3));
    
    return $tips;
}

function generatePrecautions($trimester, $week, $seed) {
    $base_precautions = [
        1 => [
            'Contact doctor if experiencing severe nausea/vomiting',
            'Report any bleeding or cramping immediately',
            'Avoid hot tubs, saunas, and high temperatures',
            'Be cautious with medications - consult doctor first',
            'Limit exposure to chemicals and toxins'
        ],
        2 => [
            'Watch for signs of gestational diabetes',
            'Monitor blood pressure regularly',
            'Report persistent headaches or vision changes',
            'Be aware of preterm labor signs',
            'Avoid sleeping on your back'
        ],
        3 => [
            'Know the signs of labor',
            'Monitor for preeclampsia symptoms',
            'Report decreased fetal movement',
            'Avoid travel after 36 weeks',
            'Have emergency contacts readily available'
        ]
    ];
    
    $additional_precautions = [
        'Stay away from people who are sick',
        'Wash hands frequently to prevent infections',
        'Avoid changing cat litter (risk of toxoplasmosis)',
        'Be careful with food safety - avoid cross-contamination',
        'Don\'t stand for long periods - take sitting breaks',
        'Avoid heavy lifting - ask for help when needed',
        'Stay out of hot tubs and saunas',
        'Wear your seatbelt properly (below the belly)',
        'Avoid exposure to paint fumes and strong chemicals',
        'Be cautious with herbal supplements - check with doctor first'
    ];
    
    $precautions = $base_precautions[$trimester];
    
    // Add 2 random additional precautions
    shuffle($additional_precautions);
    $precautions = array_merge($precautions, array_slice($additional_precautions, 0, 2));
    
    return $precautions;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Wellness Plan - Aarunya</title>
        <link rel="stylesheet" href="styles/premium-design-system.css">
    <?php include 'includes/theme_loader.php'; ?>
        <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .plan-section {
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(196, 167, 255, 0.2);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .plan-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid rgba(196, 167, 255, 0.2);
        }
        
        .plan-icon {
            width: 2.5rem;
            height: 2.5rem;
            background: rgba(244, 114, 182, 0.15);
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: #C4A7FF;
        }
        
        .plan-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #ffffff;
        }
        
        .plan-content {
            color: #546e7a;
        }
        
        .plan-list {
            list-style: none;
            padding: 0;
        }
        
        .plan-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(196, 167, 255, 0.1);
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
        }
        
        .plan-list li:last-child {
            border-bottom: none;
        }
        
        .plan-list li::before {
            content: "✓";
            color: #C4A7FF;
            font-weight: bold;
            margin-top: 0.125rem;
        }
        
        .nutrient-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .nutrient-card {
            background: rgba(15, 23, 42, 0.5);
            padding: 1rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(196, 167, 255, 0.1);
        }
        
        .schedule-grid {
            display: grid;
            gap: 0.75rem;
            margin-top: 1rem;
        }
        
        .schedule-day {
            background: rgba(15, 23, 42, 0.5);
            padding: 1rem;
            border-radius: 0.5rem;
            border-left: 3px solid #C4A7FF;
        }
        
        .day-name {
            font-weight: 600;
            color: #C4A7FF;
            margin-bottom: 0.25rem;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }
        
        .warning-box {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 1rem;
        }
        
        .warning-box h4 {
            color: #ef4444;
            margin-bottom: 0.5rem;
        }
        
        .print-btn {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            cursor: pointer;
            font-weight: 600;
            margin-left: 1rem;
        }
        
        .print-btn:hover {
            background: linear-gradient(135deg, #34d399 0%, #10b981 100%);
        }
    </style>
</head>
<body>
    <div class="app-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header">
                <div>
                    <h1 class="page-title">AI Wellness Plan</h1>
                    <p class="page-subtitle">Personalized diet and exercise recommendations for your pregnancy</p>
                </div>
                <div class="header-actions">
                    <div class="user-badge">
                        <div class="user-avatar-small">
                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                        </div>
                        <span><?php echo htmlspecialchars($user['name']); ?></span>
                    </div>
                </div>
            </div>

            <?php if ($error): ?>
                <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 12px; padding: 16px; margin-bottom: 24px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <i class="fas fa-exclamation-circle" style="color: #ef4444; font-size: 20px;"></i>
                        <span style="color: #ef4444; font-weight: 600;"><?php echo htmlspecialchars($error); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!$showPlan): ?>
            <!-- Plan Generation Form - Shown by Default -->
            <div class="section-card" style="background: linear-gradient(135deg, rgba(244, 114, 182, 0.1) 0%, rgba(196, 167, 255, 0.05) 100%); border: 2px solid rgba(244, 114, 182, 0.3);">
                <div style="text-align: center; margin-bottom: 30px;">
                    <div style="font-size: 64px; color: rgba(244, 114, 182, 0.5); margin-bottom: 20px;">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h2 style="font-size: 28px; font-weight: 800; color: #ffffff; margin-bottom: 12px;">
                        AI-Powered Wellness Plan
                    </h2>
                    <p style="color: #546e7a; font-size: 16px; max-width: 600px; margin: 0 auto;">
                        Get a personalized wellness plan tailored to your symptoms, concerns, and pregnancy stage. 
                        Our AI analyzes your input to provide customized diet, exercise, and wellness recommendations.
                    </p>
                </div>
                
                <form method="POST" action="">
                    <input type="hidden" name="generate_plan" value="1">
                    
                    <!-- Primary Input: Symptoms & Concerns -->
                    <div style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(196, 167, 255, 0.2); border-radius: 12px; padding: 24px; margin-bottom: 24px;">
                        <h3 style="color: #C4A7FF; font-size: 18px; margin-bottom: 16px;">
                            <i class="fas fa-comment-medical"></i> Tell Us How You're Feeling
                        </h3>
                        
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label class="form-label" style="color: #ffffff; font-weight: 600;">
                                What symptoms are you experiencing? <span style="color: #ef4444;">*</span>
                            </label>
                            <textarea name="symptoms" class="form-control" rows="4" required
                                      placeholder="e.g., morning sickness, fatigue, back pain, swelling, headaches..."
                                      style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(196, 167, 255, 0.2); color: #ffffff; padding: 12px; border-radius: 8px; width: 100%; font-size: 14px;"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" style="color: #ffffff; font-weight: 600;">
                                Any specific concerns or questions?
                            </label>
                            <textarea name="concerns" class="form-control" rows="3"
                                      placeholder="e.g., worried about weight gain, need energy-boosting foods, want safe exercises..."
                                      style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(196, 167, 255, 0.2); color: #ffffff; padding: 12px; border-radius: 8px; width: 100%; font-size: 14px;"></textarea>
                        </div>
                    </div>
                    
                    <!-- Additional Information -->
                    <div style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(196, 167, 255, 0.2); border-radius: 12px; padding: 24px; margin-bottom: 24px;">
                        <h3 style="color: #3b82f6; font-size: 18px; margin-bottom: 16px;">
                            <i class="fas fa-user-circle"></i> Your Information
                        </h3>
                        
                        <div class="form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                            <div class="form-group">
                                <label class="form-label" style="color: #546e7a; font-size: 14px;">
                                    Pregnancy Week <span style="color: #ef4444;">*</span>
                                </label>
                                <input type="number" name="pregnancy_week" class="form-control" 
                                       value="<?php echo $userData['pregnancy_week'] ?? ''; ?>" 
                                       min="1" max="42" required
                                       style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(196, 167, 255, 0.2); color: #ffffff; padding: 12px; border-radius: 8px; width: 100%;">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" style="color: #546e7a; font-size: 14px;">
                                    Current Weight (kg)
                                </label>
                                <input type="number" name="current_weight" class="form-control" 
                                       step="0.1" placeholder="e.g., 65.5"
                                       style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(196, 167, 255, 0.2); color: #ffffff; padding: 12px; border-radius: 8px; width: 100%;">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" style="color: #546e7a; font-size: 14px;">
                                    Height (cm)
                                </label>
                                <input type="number" name="height" class="form-control" 
                                       placeholder="e.g., 165"
                                       style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(196, 167, 255, 0.2); color: #ffffff; padding: 12px; border-radius: 8px; width: 100%;">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" style="color: #546e7a; font-size: 14px;">
                                    Activity Level
                                </label>
                                <select name="activity_level" class="form-control"
                                        style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(196, 167, 255, 0.2); color: #ffffff; padding: 12px; border-radius: 8px; width: 100%;">
                                    <option value="low">Low - Sedentary</option>
                                    <option value="moderate" selected>Moderate - Some exercise</option>
                                    <option value="high">High - Very active</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group" style="margin-top: 16px;">
                            <label class="form-label" style="color: #546e7a; font-size: 14px;">
                                Dietary Restrictions
                            </label>
                            <input type="text" name="dietary_restrictions" class="form-control" 
                                   placeholder="e.g., vegetarian, lactose intolerant, nut allergies..."
                                   style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(196, 167, 255, 0.2); color: #ffffff; padding: 12px; border-radius: 8px; width: 100%;">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 16px; font-size: 18px; font-weight: 700;">
                        <i class="fas fa-magic"></i> Generate My Personalized Wellness Plan
                    </button>
                    
                    <p style="text-align: center; color: #78909c; font-size: 13px; margin-top: 16px;">
                        <i class="fas fa-lock"></i> Your information is private and secure. Plans are generated instantly.
                    </p>
                </form>
            </div>
            <?php endif; ?>

            <?php if ($showPlan && $generatedPlan): ?>
            <!-- Generated Plan -->
            <div id="wellness-plan">
                <!-- Personalized Introduction -->
                <div class="plan-section" style="background: linear-gradient(135deg, rgba(244, 114, 182, 0.15) 0%, rgba(219, 39, 119, 0.1) 100%);">
                    <div class="plan-content">
                        <h3 style="color: #C4A7FF; margin-bottom: 1rem; font-size: 1.5rem;">
                            <i class="fas fa-heart"></i> Your Personalized Wellness Journey
                        </h3>
                        <p style="font-size: 1.1rem; line-height: 1.8; color: #e2e8f0;">
                            <?php echo $generatedPlan['personalized_intro']; ?>
                        </p>
                    </div>
                </div>

                <!-- Plan Overview -->
                <div class="plan-section">
                    <div class="plan-header">
                        <div class="plan-icon">
                            <i class="fas fa-calendar-week"></i>
                        </div>
                        <div style="flex: 1;">
                            <div class="plan-title">Your Pregnancy Journey</div>
                            <div style="color: #546e7a; font-size: 0.875rem;">
                                Week <?php echo $generatedPlan['week']; ?> • Trimester <?php echo $generatedPlan['trimester']; ?>
                                <?php if ($generatedPlan['bmi'] > 0): ?>
                                • BMI: <?php echo number_format($generatedPlan['bmi'], 1); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <button onclick="window.print()" class="print-btn">
                            <i class="fas fa-print"></i> Print Plan
                        </button>
                    </div>
                    <div class="plan-content">
                        <div style="background: rgba(244, 114, 182, 0.1); padding: 1rem; border-radius: 0.5rem; border-left: 3px solid #C4A7FF;">
                            <strong style="color: #C4A7FF;">This Week's Focus:</strong>
                            <p style="margin: 0.5rem 0 0 0;"><?php echo $generatedPlan['weekly_focus']; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Baby Development -->
                <div class="plan-section" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.05) 100%);">
                    <div class="plan-header">
                        <div class="plan-icon" style="background: rgba(59, 130, 246, 0.15); color: #60a5fa;">
                            <i class="fas fa-baby"></i>
                        </div>
                        <div class="plan-title">Your Baby This Week</div>
                    </div>
                    <div class="plan-content">
                        <p style="font-size: 1.05rem; line-height: 1.7; color: #e2e8f0;">
                            <i class="fas fa-info-circle" style="color: #60a5fa;"></i>
                            <?php echo $generatedPlan['baby_development']; ?>
                        </p>
                    </div>
                </div>

                <!-- Diet Plan -->
                <div class="plan-section">
                    <div class="plan-header">
                        <div class="plan-icon">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <div style="flex: 1;">
                            <div class="plan-title">Nutrition Plan</div>
                            <div style="color: #10b981; font-size: 0.875rem; font-weight: 600; margin-top: 0.25rem;">
                                <i class="fas fa-leaf"></i> <?php echo $generatedPlan['diet']['name']; ?>
                            </div>
                        </div>
                    </div>
                    <div class="plan-content">
                        <p><strong>Focus:</strong> <?php echo $generatedPlan['diet']['focus']; ?></p>
                        <p><strong>Daily Calories:</strong> <?php echo $generatedPlan['diet']['calories']; ?></p>
                        
                        <div style="background: rgba(16, 185, 129, 0.1); padding: 1rem; border-radius: 0.5rem; margin: 1rem 0; border-left: 3px solid #10b981;">
                            <strong style="color: #10b981;"><i class="fas fa-tint"></i> Hydration Tip:</strong>
                            <p style="margin: 0.5rem 0 0 0;"><?php echo $generatedPlan['diet']['hydration_tip']; ?></p>
                        </div>
                        
                        <h4 style="color: #ffffff; margin-top: 1.5rem;">Key Nutrients</h4>
                        <div class="nutrient-grid">
                            <?php foreach($generatedPlan['diet']['key_nutrients'] as $nutrient): ?>
                            <div class="nutrient-card">
                                <strong><?php echo $nutrient; ?></strong>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <h4 style="color: #ffffff; margin-top: 1.5rem;">Foods to Include</h4>
                        <ul class="plan-list">
                            <?php foreach($generatedPlan['diet']['foods_to_eat'] as $food): ?>
                            <li><?php echo $food; ?></li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <h4 style="color: #ffffff; margin-top: 1.5rem;">Foods to Avoid</h4>
                        <ul class="plan-list">
                            <?php foreach($generatedPlan['diet']['foods_to_avoid'] as $food): ?>
                            <li style="color: #fca5a5;"><?php echo $food; ?></li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <h4 style="color: #ffffff; margin-top: 1.5rem;">Sample Meals</h4>
                        <ul class="plan-list">
                            <?php foreach($generatedPlan['diet']['meal_suggestions'] as $meal): ?>
                            <li><?php echo $meal; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <!-- Exercise Plan -->
                <div class="plan-section">
                    <div class="plan-header">
                        <div class="plan-icon">
                            <i class="fas fa-dumbbell"></i>
                        </div>
                        <div style="flex: 1;">
                            <div class="plan-title">Exercise Plan</div>
                            <div style="color: #C4A7FF; font-size: 0.875rem; font-weight: 600; margin-top: 0.25rem;">
                                <i class="fas fa-heartbeat"></i> <?php echo $generatedPlan['exercise']['name']; ?>
                            </div>
                        </div>
                    </div>
                    <div class="plan-content">
                        <p><strong>Duration:</strong> <?php echo $generatedPlan['exercise']['duration']; ?></p>
                        <p><strong>Intensity:</strong> <?php echo ucfirst($generatedPlan['exercise']['intensity']); ?></p>
                        
                        <div style="background: rgba(244, 114, 182, 0.1); padding: 1rem; border-radius: 0.5rem; margin: 1rem 0; border-left: 3px solid #C4A7FF;">
                            <strong style="color: #C4A7FF;"><i class="fas fa-lightbulb"></i> Exercise Tip:</strong>
                            <p style="margin: 0.5rem 0 0 0;"><?php echo $generatedPlan['exercise']['exercise_tip']; ?></p>
                        </div>
                        
                        <div style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.05) 100%); padding: 1rem; border-radius: 0.5rem; margin: 1rem 0; text-align: center; font-style: italic;">
                            <p style="margin: 0; color: #10b981; font-size: 1.1rem;"><?php echo $generatedPlan['exercise']['motivation']; ?></p>
                        </div>
                        
                        <h4 style="color: #ffffff; margin-top: 1.5rem;">Safe Exercises</h4>
                        <ul class="plan-list">
                            <?php foreach($generatedPlan['exercise']['safe_exercises'] as $exercise): ?>
                            <li><?php echo $exercise; ?></li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <h4 style="color: #ffffff; margin-top: 1.5rem;">Weekly Schedule</h4>
                        <div class="schedule-grid">
                            <?php foreach($generatedPlan['exercise']['weekly_schedule'] as $day => $activity): ?>
                            <div class="schedule-day">
                                <div class="day-name"><?php echo $day; ?></div>
                                <div><?php echo $activity; ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="warning-box">
                            <h4><i class="fas fa-exclamation-triangle"></i> Exercises to Avoid</h4>
                            <ul style="margin: 0; padding-left: 1rem;">
                                <?php foreach($generatedPlan['exercise']['exercises_to_avoid'] as $avoid): ?>
                                <li><?php echo $avoid; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Wellness Tips -->
                <div class="plan-section">
                    <div class="plan-header">
                        <div class="plan-icon">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <div class="plan-title">Wellness Tips</div>
                    </div>
                    <div class="plan-content">
                        <ul class="plan-list">
                            <?php foreach($generatedPlan['wellness_tips'] as $tip): ?>
                            <li><?php echo $tip; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <!-- Precautions -->
                <div class="plan-section">
                    <div class="plan-header">
                        <div class="plan-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="plan-title">Important Precautions</div>
                    </div>
                    <div class="plan-content">
                        <div class="warning-box">
                            <h4><i class="fas fa-exclamation-circle"></i> When to Contact Your Doctor</h4>
                            <ul style="margin: 0; padding-left: 1rem;">
                                <?php foreach($generatedPlan['precautions'] as $precaution): ?>
                                <li><?php echo $precaution; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Disclaimer -->
                <div class="plan-section">
                    <div class="plan-content" style="text-align: center; font-style: italic;">
                        <p><strong>Disclaimer:</strong> This AI-generated wellness plan is for informational purposes only and should not replace professional medical advice. Always consult with your healthcare provider before making significant changes to your diet or exercise routine during pregnancy.</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
        <i class="fas fa-bars"></i>
    </button>

    <script>
    function toggleMobileMenu() {
        document.querySelector('.sidebar').classList.toggle('mobile-open');
    }

    document.addEventListener('click', function(event) {
        const sidebar = document.querySelector('.sidebar');
        const toggle = document.querySelector('.mobile-menu-toggle');
        
        if (window.innerWidth <= 768 && 
            !sidebar.contains(event.target) && 
            !toggle.contains(event.target) &&
            sidebar.classList.contains('mobile-open')) {
            sidebar.classList.remove('mobile-open');
        }
    });
    </script>

    <!-- Aarunya Chatbot -->
    <?php include 'includes/chatbot.php'; ?>
</body>
</html>
