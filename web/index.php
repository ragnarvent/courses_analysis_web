<?php
define('MAX_ROWS', 100);
$loader = require_once __DIR__ . '/../vendor/autoload.php';
$loader->add('BIS', __DIR__. '/..');

$app = new Silex\Application();
$app['debug'] = true;

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_sqlite',
        'path'     => __DIR__ . '/../db/courses.sqlite',
    ),
));

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../views',
));

$app->get('/', function() use ($app) {
    return $app['twig']->render('index.twig', array(
        'courses' => BIS\Course::getAll($app['db']),
        'corpus_words' => json_encode(BIS\CorpusWord::topWords($app['db'], MAX_ROWS)),
    ));
});

$app->get('/about', function() use ($app) {
    return $app['twig']->render('about.twig', array(
        'courses' => BIS\Course::getAll($app['db']),
        'lda_loglikelihood' => json_encode(BIS\LdaLoglikelihood::all($app['db'])),
    ));
});

$app->get('/corpus', function(Silex\Application $app) {
    // $sql = 'SELECT `word`, `count` FROM `corpusword` ORDER BY `count` DESC LIMIT ?';
    // $colors = BIS\RandomColor::get(MAX_ROWS);
    // $data = array();
    // foreach ($app['db']->fetchAll($sql, array(MAX_ROWS)) as $i => $row) {
    //     $data[] = array(
    //         'label' => $row['word'],
    //         'value' => intval($row['count']),
    //         'color' => $colors[$i]
    //     );
    // }
    // return $app->json($data);
    return $app['twig']->render('corpus.twig', array(
        'rows' => MAX_ROWS,
        // 'data' => json_encode($data),
        'corpus_words' => json_encode(BIS\CorpusWord::topWords($app['db'], MAX_ROWS)),
        'courses' => BIS\Course::getAll($app['db']),
        'summary' => BIS\CorpusWord::summary($app['db']),
    ));
});

$app->get('/corpus/{courseId}', function(Silex\Application $app, $courseId) {
    // $colors = BIS\RandomColor::get(MAX_ROWS);
    // $sql = 'SELECT `word`, `count` FROM `courseword` WHERE `course_id` = ? ORDER BY `count` DESC LIMIT ?';
    // $data = array();
    // foreach ($app['db']->fetchAll($sql, array($courseId, MAX_ROWS)) as $i => $row) {
    //     $data[] = array(
    //         'label' => $row['word'],
    //         'value' => intval($row['count']),
    //         'color' => $colors[$i]
    //     );
    // }

    return $app['twig']->render('corpus.twig', array(
        'rows' => MAX_ROWS,
        // 'data' => json_encode($data),
        'corpus_words' => json_encode(BIS\CourseWord::topWords($app['db'], MAX_ROWS, $courseId)),
        // 'words' => json_encode(BIS\CourseWord::topWordsLabels($app['db'], MAX_ROWS, $courseId)),
        'courses' => BIS\Course::getAll($app['db']),
        'course'  => BIS\Course::getRecord($app['db'], $courseId),
        'summary' => BIS\CourseWord::summary($app['db'], $courseId),
        'topics' => BIS\Topic::course($app['db'], $courseId),
        'topic_weights' => json_encode(BIS\Topic::courseTopicWeights($app['db'], $courseId)),
        // 'topic_words' => json_encode(BIS\Topic::getAllWordsByCourse($app['db'], $courseId)),
        // 'map_courses' => json_encode(BIS\Course::getAllNamesByCourse($app['db'], $courseId)),
        // 'map_topics' => json_encode(BIS\Topic::getAllNamesByCourse($app['db'], $courseId)),
        // 'map_data' => json_encode(BIS\Topic::courseTopicsByCourse($app['db'], $courseId)),
    ));
})
->assert('courseId', '\d+');

$app->get('/topics', function(Silex\Application $app) {
    return $app['twig']->render('topics.twig', array(
        'courses' => BIS\Course::getAll($app['db']),
        'topics' => BIS\Topic::all($app['db']),
    ));
});

$app->get('/course_topics', function(Silex\Application $app) {
    return $app['twig']->render('course_topics.twig', array(
        'courses' => BIS\Course::getAll($app['db']),
        'topics' => BIS\Topic::all($app['db']),
        'topic_words' => json_encode(BIS\Topic::getAllWords($app['db'])),
        'map_courses' => json_encode(BIS\Course::getAllNames($app['db'])),
        'map_topics' => json_encode(BIS\Topic::getAllNames($app['db'])),
        'map_data' => json_encode(BIS\Topic::courseTopics($app['db'])),
    ));
});

// $app->get('/course', function() use ($app) {
//     $sql = 'SELECT * FROM course ORDER BY ?';
//     $data = $app['db']->fetchAll($sql, array('name'));
//
//     return $app->json($data);
// });

$app->run();