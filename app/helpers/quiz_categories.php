<?php
/**
 * Quiz Categories Helper
 * Organizes quizzes by category and part
 */

require_once __DIR__ . '/quiz_functions.php';

/**
 * Get B1 quizzes organized by category
 * 
 * @param array $quizzes All quizzes with names
 * @return array Organized quizzes by category
 */
function getB1QuizzesByCategory($quizzes) {
    $organized = [
        'lesen' => [
            'part1' => [],
            'part2' => [],
            'part3' => []
        ],
        'sprachbausteine' => [
            'part1' => [],
            'part2' => []
        ],
        'hoeren' => [],
        'schreiben' => []
    ];
    
    // B1 Lesen Teil 1
    $lesen1 = ['204.html', '206.html', '215.html', '219.html', '221.html', '223.html', '226.html', '227.html', '228.html', '230.html', '232.html', '234.html', '235.html', '237.html', '240.html', '242.html', '244.html', '246.html', '247.html'];
    
    // B1 Lesen Teil 2
    $lesen2 = ['205.html', '207.html', '208.html', '216.html', '220.html', '222.html', '224.html', '225.html', '229.html', '231.html', '233.html', '236.html', '238.html', '239.html', '241.html', '243.html', '245.html', '248.html', '249.html'];
    
    // B1 Lesen Teil 3
    $lesen3 = ['201.html', '203.html', '202.html', '217.html', '218.html', '286.html', '287.html', '288.html'];
    
    // B1 Sprachbausteine Teil 1
    $sprach1 = ['209.html', '211.html', '213.html', '250.html', '251.html', '254.html', '257.html', '259.html', '261.html', '262.html', '264.html', '266.html', '268.html', '269.html', '273.html', '278.html', '280.html', '282.html', '283.html'];
    
    // B1 Sprachbausteine Teil 2
    $sprach2 = ['210.html', '212.html', '214.html', '253.html', '252.html', '255.html', '256.html', '258.html', '260.html', '263.html', '265.html', '267.html', '270.html', '272.html', '274.html', '276.html', '277.html', '279.html', '281.html', '284.html'];
    
    // B1 Hören
    $hoeren = ['271.html', '285.html', '275.html'];
    
    foreach ($quizzes as $quiz) {
        $file = $quiz['file'];
        
        if (in_array($file, $lesen1)) {
            $organized['lesen']['part1'][] = $quiz;
        } elseif (in_array($file, $lesen2)) {
            $organized['lesen']['part2'][] = $quiz;
        } elseif (in_array($file, $lesen3)) {
            $organized['lesen']['part3'][] = $quiz;
        } elseif (in_array($file, $sprach1)) {
            $organized['sprachbausteine']['part1'][] = $quiz;
        } elseif (in_array($file, $sprach2)) {
            $organized['sprachbausteine']['part2'][] = $quiz;
        } elseif (in_array($file, $hoeren)) {
            $organized['hoeren'][] = $quiz;
        }
    }
    
    return $organized;
}

/**
 * Get B2 quizzes organized by category
 * 
 * @param array $quizzes All quizzes with names
 * @return array Organized quizzes by category
 */
function getB2QuizzesByCategory($quizzes) {
    $organized = [
        'lesen' => [
            'part1' => [],
            'part2' => [],
            'part3' => []
        ],
        'sprachbausteine' => [
            'part1' => [],
            'part2' => []
        ],
        'hoeren' => []
    ];
    
    // B2 Lesen Teil 1
    $lesen1 = ['11.html', '12.html', '16.html', '17.html', '18.html', '25.html', '26.html', '27.html', '28.html', '39.html', '40.html', '41.html', '42.html', '143.html', '43.html', '59.html', '70.html', '71.html', '72.html', '73.html', '74.html', '64.html', '84.html', '86.html', '85.html', '98.html', '101.html', '136.html'];
    
    // B2 Lesen Teil 2
    $lesen2 = ['4.html', '5.html', '6.html', '7.html', '13.html', '15.html', '19.html', '20.html', '21.html', '22.html', '23.html', '24.html', '134.html', '29.html', '44.html', '45.html', '46.html', '47.html', '60.html', '61.html', '75.html', '77.html', '79.html', '102.html', '123.html', '131.html'];
    
    // B2 Lesen Teil 3
    $lesen3 = ['62.html', '63.html', '65.html', '66.html', '145.html', '67.html', '68.html', '69.html', '141.html', '76.html', '78.html', '80.html', '81.html', '82.html', '83.html', '87.html', '88.html', '89.html', '90.html', '108.html', '109.html', '110.html', '111.html', '124.html', '132.html', '140.html'];
    
    // B2 Sprachbausteine Teil 1
    $sprach1 = ['30.html', '31.html', '32.html', '33.html', '34.html', '35.html', '91.html', '36.html', '37.html', '38.html', '92.html', '144.html', '93.html', '94.html', '95.html', '96.html', '97.html', '100.html', '103.html', '105.html', '106.html', '114.html', '115.html', '118.html', '119.html', '126.html', '128.html', '130.html', '133.html', '135.html', '137.html', '138.html', '142.html'];
    
    // B2 Sprachbausteine Teil 2
    $sprach2 = ['1.html', '2.html', '3.html', '14.html', '48.html', '49.html', '50.html', '51.html', '52.html', '53.html', '54.html', '55.html', '56.html', '57.html', '58.html', '99.html', '104.html', '107.html', '112.html', '113.html', '116.html', '117.html', '120.html', '121.html', '122.html', '125.html', '127.html', '129.html', '139.html'];
    
    // B2 Hören
    $hoeren = ['8.html', '9.html', '10.html'];
    
    foreach ($quizzes as $quiz) {
        $file = $quiz['file'];
        
        if (in_array($file, $lesen1)) {
            $organized['lesen']['part1'][] = $quiz;
        } elseif (in_array($file, $lesen2)) {
            $organized['lesen']['part2'][] = $quiz;
        } elseif (in_array($file, $lesen3)) {
            $organized['lesen']['part3'][] = $quiz;
        } elseif (in_array($file, $sprach1)) {
            $organized['sprachbausteine']['part1'][] = $quiz;
        } elseif (in_array($file, $sprach2)) {
            $organized['sprachbausteine']['part2'][] = $quiz;
        } elseif (in_array($file, $hoeren)) {
            $organized['hoeren'][] = $quiz;
        }
    }
    
    return $organized;
}

