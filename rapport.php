<?php
// Données simulées avec URL pour chaque question
$questions = [
    ['sent_date' => '2025-04-07', 'question' => 'Quelle est la capitale de la France ?', 'answer' => 'Paris', 'quiz_title' => 'Géographie', 'viewed' => true, 'url' => 'https://monapp.test/question/1'],
    ['sent_date' => '2025-04-08', 'question' => 'Quelle est la plus grande planète ?', 'answer' => 'Jupiter', 'quiz_title' => 'Astronomie', 'viewed' => false, 'url' => 'https://monapp.test/question/2'],
    ['sent_date' => '2025-04-09', 'question' => 'Qui a peint la Joconde ?', 'answer' => 'Léonard de Vinci', 'quiz_title' => 'Art', 'viewed' => true, 'url' => 'https://monapp.test/question/3'],
    ['sent_date' => '2025-04-10', 'question' => 'Quelle est la racine carrée de 64 ?', 'answer' => '8', 'quiz_title' => 'Mathématiques', 'viewed' => false, 'url' => 'https://monapp.test/question/4'],
    ['sent_date' => '2025-04-11', 'question' => 'Quelle est la langue officielle du Brésil ?', 'answer' => 'Portugais', 'quiz_title' => 'Langues', 'viewed' => true, 'url' => 'https://monapp.test/question/5'],
    ['sent_date' => '2025-04-12', 'question' => 'Combien y a-t-il de continents ?', 'answer' => '7', 'quiz_title' => 'Géographie', 'viewed' => true, 'url' => 'https://monapp.test/question/6'],
    ['sent_date' => '2025-04-13', 'question' => 'Quelle est la formule de l’eau ?', 'answer' => 'H2O', 'quiz_title' => 'Chimie', 'viewed' => false, 'url' => 'https://monapp.test/question/7'],
];

// Génération des jours ouvrables
$start = new DateTime('2025-04-07');
$end = new DateTime('2025-04-11');
$days = [];

while ($start <= $end) {
    $dateStr = $start->format('Y-m-d');
    $dayName = strftime('%A', $start->getTimestamp());
    $days[$dateStr] = ['label' => ucfirst($dayName), 'questions' => []];
    $start->modify('+1 day');
}

// Remplir les questions par jour
foreach ($questions as $q) {
    if (isset($days[$q['sent_date']])) {
        $days[$q['sent_date']]['questions'][] = $q;
    }
}

// Progress bar
$total = count($questions);
$seen = count(array_filter($questions, fn($q) => $q['viewed']));
$progress = round(($seen / $total) * 100);

// Questions non vues par questionnaire
$notViewedByQuiz = [];
foreach ($questions as $q) {
    if (!$q['viewed']) {
        $notViewedByQuiz[$q['quiz_title']][] = $q;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport hebdo avec liens</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            color: #333;
        }

        h1 {
            margin-bottom: 10px;
            font-size: 24px;
        }

        .progress-container {
            background-color: #eee;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 30px;
        }

        .progress-bar {
            height: 20px;
            background-color: #4caf50;
            width: <?= $progress ?>%;
            text-align: center;
            color: white;
            font-size: 12px;
            line-height: 20px;
        }

        .day-block {
            margin-bottom: 30px;
            padding: 15px;
            border-left: 5px solid #3f51b5;
            background-color: #f5f7ff;
        }

        .day-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 12px;
            color: #3f51b5;
        }

        .question-item {
            margin-left: 15px;
            margin-bottom: 10px;
        }

        .quiz-title {
            font-weight: bold;
            color: #2a4d9b;
        }

        .question-link {
            margin-left: 25px;
            font-size: 13px;
        }

        .question-link a {
            color: #007BFF;
            text-decoration: none;
        }

        .section-title {
            margin-top: 50px;
            font-size: 22px;
            border-bottom: 2px solid #ccc;
            padding-bottom: 5px;
            margin-bottom: 20px;
        }

        .quiz-block {
            margin-bottom: 25px;
            padding: 12px;
            background-color: #fff5f5;
            border-left: 5px solid #e53935;
        }

        .quiz-block h3 {
            margin: 0 0 10px;
            color: #e53935;
        }

        .sent-date {
            font-size: 13px;
            font-style: italic;
            color: #666;
            margin-left: 8px;
        }
    </style>
</head>
<body>

<h1>📅 Rapport d’activité — semaine du 7 au 11 avril 2025</h1>

<!-- Progress bar -->
<div class="progress-container">
    <div class="progress-bar"><?= $progress ?>%</div>
</div>

<!-- Partie 1 : par jour de la semaine -->
<?php foreach ($days as $date => $info): ?>
    <div class="day-block">
        <div class="day-title"><?= $info['label'] ?> (<?= date('d/m', strtotime($date)) ?>)</div>
        <?php if (count($info['questions']) === 0): ?>
            <p class="question-item"><em>Aucune question ce jour-là</em></p>
        <?php else: ?>
            <?php foreach ($info['questions'] as $q): ?>
                <div class="question-item">
                    <span class="quiz-title">📘 <?= htmlspecialchars($q['quiz_title']) ?></span><br>
                    📌 <?= htmlspecialchars($q['question']) ?><br>
                    <div class="question-link">
                        🔗 <a href="<?= htmlspecialchars($q['url']) ?>" target="_blank">Voir la question</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<!-- Partie 2 : non consultées -->
<div class="section-title">❌ Questions non encore consultées</div>

<?php if (count($notViewedByQuiz) === 0): ?>
    <p>Toutes les questions ont été consultées cette semaine, bravo ! 🎉</p>
<?php else: ?>
    <?php foreach ($notViewedByQuiz as $quiz => $items): ?>
        <div class="quiz-block">
            <h3>📘 <?= htmlspecialchars($quiz) ?></h3>
            <?php foreach ($items as $q): ?>
                <div class="question-item">
                    📌 <?= htmlspecialchars($q['question']) ?>
                    <span class="sent-date">(envoyée le <?= date('d/m', strtotime($q['sent_date'])) ?>)</span><br>
                    <div class="question-link">
                        🔗 <a href="<?= htmlspecialchars($q['url']) ?>" target="_blank">Voir la question</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>

