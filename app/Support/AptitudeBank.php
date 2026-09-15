<?php

namespace App\Support;

class AptitudeBank
{
    /**
     * @return list<array{prompt: string, options: list<string>, correct: int, tag: string, tip: string}>
     */
    public static function questions(string $examSlug = 'bba'): array
    {
        $shared = [
            ['A shirt marked at 800 is sold at 20% discount. Selling price is:', ['640', '680', '720', '760'], 0, 'Quant', 'SP = 800 × 0.80 = 640.'],
            ['30% of 250 is:', ['65', '70', '75', '80'], 2, 'Quant', '0.3 × 250 = 75.'],
            ['If A:B = 2:3 and B:C = 3:5, then A:C is:', ['2:5', '3:5', '2:3', '5:2'], 0, 'Quant', 'B cancels: 2:5.'],
            ['Average of 4, 8, 12 is:', ['6', '8', '10', '24'], 1, 'Quant', '(4+8+12)/3 = 8.'],
            ['A train covers 120 km in 2 hours. Speed is:', ['40 km/h', '50 km/h', '60 km/h', '80 km/h'], 2, 'Quant', '120/2 = 60.'],
            ['Simple interest on 2000 at 10% for 2 years is:', ['200', '400', '420', '2400'], 1, 'Quant', 'SI = 2000×10×2/100 = 400.'],
            ['If 5x = 20, x equals:', ['2', '3', '4', '5'], 2, 'Quant', 'x = 4.'],
            ['The next number in 2, 6, 12, 20, 30, … is:', ['36', '40', '42', '44'], 2, 'Logic', 'Add 4, 6, 8, 10, then 12 → 42.'],
            ['All roses are flowers. Some flowers fade. Which must be true?', ['All roses fade', 'Some roses fade', 'No rose fades', 'None of these must be true'], 3, 'Logic', 'The fading flowers might not be roses.'],
            ['Find the odd one: 3, 5, 7, 9, 11', ['3', '7', '9', '11'], 2, 'Logic', '9 is not prime.'],
            ['If CAT = 24 and DOG = 26, then BAT =', ['23', '24', '25', '26'], 0, 'Logic', 'Position sum: B+A+T = 2+1+20 = 23.'],
            ['Choose the synonym of “brief”:', ['lengthy', 'short', 'noisy', 'rare'], 1, 'Verbal', 'Brief = short.'],
            ['Choose the antonym of “scarce”:', ['rare', 'plentiful', 'tiny', 'hidden'], 1, 'Verbal', 'Scarce is the opposite of plentiful.'],
            ['Fill in: She _____ to campus every day.', ['walk', 'walks', 'walking', 'walked always'], 1, 'Verbal', 'Third person singular present: walks.'],
            ['Error: “The team are won yesterday.” Correct verb idea is:', ['are winning', 'won', 'is won', 'have win'], 1, 'Verbal', 'Simple past: The team won yesterday.'],
            ['A passage says students who sleep 7 hours score higher. We can infer:', ['Sleep is the only factor', 'The study found a link between sleep and scores', 'No student sleeps 7 hours', 'Scores cause sleep'], 1, 'RC', 'Stay inside what the sentence claims.'],
            ['“However” in a passage usually signals:', ['an example', 'a contrast', 'a definition', 'a date'], 1, 'RC', 'However = contrast.'],
            ['If the cost is 150 and profit is 20%, SP is:', ['170', '180', '185', '200'], 1, 'Quant', '150 × 1.20 = 180.'],
            ['Work: A finishes in 10 days, B in 15. Together they finish in:', ['5 days', '6 days', '8 days', '12 days'], 1, 'Quant', '1/10+1/15 = 1/6 per day → 6 days.'],
            ['A bag has 3 red and 2 blue balls. P(red) is:', ['2/5', '3/5', '1/2', '3/2'], 1, 'Quant', '3 out of 5.'],
            ['12.5% of 80 is:', ['8', '10', '12', '16'], 1, 'Quant', '1/8 of 80 = 10.'],
            ['If 40% of a number is 60, the number is:', ['120', '140', '150', '180'], 2, 'Quant', 'n × 0.4 = 60 → n = 150.'],
            ['Median of 2, 9, 3, 7, 5 is:', ['3', '5', '7', '9'], 1, 'Quant', 'Ordered 2,3,5,7,9 → 5.'],
            ['Compound: which is a compound word?', ['run', 'notebook', 'quickly', 'is'], 1, 'Verbal', 'notebook = note + book.'],
            ['Analogies: Book is to Read as Fork is to:', ['Kitchen', 'Eat', 'Metal', 'Plate'], 1, 'Verbal', 'Object to its use.'],
            ['If all P are Q and all Q are R, then:', ['All P are R', 'All R are P', 'No P is R', 'Some R are not Q'], 0, 'Logic', 'Chain of inclusion.'],
            ['Series: 81, 27, 9, 3, …', ['1', '0', '2', '6'], 0, 'Logic', 'Divide by 3 each time.'],
            ['A clock shows 3:15. The angle between hands is closest to:', ['0°', '7.5°', '90°', '180°'], 1, 'Quant', 'Minute hand at 90°, hour hand 7.5° past 90° → 7.5°.'],
            ['Which sentence is most precise?', ['People always hate maths', 'Many students in this sample found the quant section harder', 'Quant is useless', 'Nobody can learn ratios'], 1, 'Verbal', 'Avoid absolutes; stay with the sample.'],
            ['Data: 2, 2, 2, 10. The mean is pulled up by:', ['the mode', 'the outlier 10', 'the median', 'the range being 0'], 1, 'Quant', 'One large value raises the mean.'],
        ];

        if ($examSlug === 'mba') {
            $rows = array_merge($shared, [
                ['A study finds coffee drinkers have higher grades. To weaken:', ['Some tea drinkers also study', 'Coffee drinkers may already be high performers who also drink coffee', 'Coffee contains caffeine', 'Grades are numbers'], 1, 'CR', 'A confound: another cause can explain both.'],
                ['Conclusion: Ban night flights to cut noise. Which is an assumption?', ['People prefer trains', 'Night flights are a major source of the noise being discussed', 'Airports make profit', 'Pilots work days'], 1, 'CR', 'The ban only works if night flights cause the problem.'],
                ['To strengthen “practice tests raise scores”:', ['Students like apps', 'A group that took weekly mocks gained more than a similar group that did not', 'Tests are stressful', 'Some students skip breakfast'], 1, 'CR', 'A comparison with a control strengthens cause.'],
                ['The argument: “Sales rose after the ad, so the ad worked.” The gap is:', ['Sales numbers can be large', 'Something else may have caused the rise', 'Ads are expensive', 'The company has a logo'], 1, 'CR', 'Post hoc is not proof.'],
                ['RC inference must be:', ['the author’s life story', 'something required by the passage', 'today’s newspaper view', 'the opposite of a topic sentence'], 1, 'RC', 'Inference = must be true from the text.'],
                ['If x + y = 10 and x − y = 2, xy equals:', ['16', '21', '24', '48'], 2, 'Quant', 'x=6, y=4 → 24.'],
                ['A mixture is 20% acid. 40 L of it contains how much acid?', ['4 L', '6 L', '8 L', '10 L'], 2, 'Quant', '20% of 40 = 8.'],
                ['Which weakens “remote work always cuts productivity”?', ['Some firms saw output rise after going remote', 'Offices have chairs', 'Email exists', 'Managers like meetings'], 0, 'CR', 'A counterexample hits “always”.'],
                ['Main idea questions ask for:', ['a tiny example', 'the passage’s central claim', 'the first date mentioned', 'a word definition only'], 1, 'RC', 'Central claim, not a detail.'],
                ['If 3 people split 180 in ratio 2:3:4, the largest share is:', ['40', '60', '80', '90'], 2, 'Quant', '2+3+4=9 parts; 4/9 × 180 = 80.'],
                ['A case prompt wants:', ['every possible idea', 'one decision plus why and a risk', 'a history essay', 'a list of slogans'], 1, 'CR', 'Decide, justify, flag risk.'],
                ['Overlapping sets: 40 speak English, 30 Hindi, 10 both, in a class of 60. Speak neither?', ['0', '10', '20', '30'], 0, 'Quant', '40+30−10=60; neither = 0.'],
            ]);
        } else {
            $rows = array_merge($shared, [
                ['A shopkeeper buys at 90 and wants 20% profit on cost. Marked price if no discount:', ['100', '108', '112', '118'], 1, 'Quant', '90 × 1.2 = 108.'],
                ['6 workers finish in 8 days. 8 workers finish in:', ['4 days', '6 days', '8 days', '10 days'], 1, 'Quant', 'Work = 48 worker-days; 48/8 = 6.'],
                ['Find 15% of 15% of 200:', ['3.5', '4.5', '5', '6'], 1, 'Quant', '0.15 × 0.15 × 200 = 4.5.'],
                ['If 2 pencils cost 8, 5 pencils cost:', ['16', '18', '20', '24'], 2, 'Quant', 'Unit 4; 5×4=20.'],
                ['Which is a statement of fact, not opinion?', ['This exam is unfair', 'The paper has 100 questions', 'Quant is fun', 'Everyone hates GK'], 1, 'Verbal', 'Countable paper fact.'],
                ['Arrange: (1) exam (2) application (3) result (4) preparation', ['2-4-1-3', '4-2-1-3', '2-1-4-3', '4-1-2-3'], 0, 'Logic', 'Apply, prepare, sit, result.'],
                ['If SOUTH is coded as 7-12-6-7-9 by a simple +1 letter number, watch the pattern. Safer: next of 2,4,8,16 is:', ['18', '24', '32', '64'], 2, 'Logic', 'Multiply by 2 → 32.'],
                ['A pie chart shows 90° on “fees”. That slice is:', ['15%', '25%', '30%', '90%'], 1, 'Quant', '90/360 = 25%.'],
                ['Choose correct article: She is _____ honest officer.', ['a', 'an', 'the', 'no article'], 1, 'Verbal', 'Honest starts with a vowel sound.'],
                ['Time: 9:00 to 11:30 is how many minutes?', ['120', '130', '150', '180'], 2, 'Quant', '2 h 30 min = 150.'],
                ['If selling two items at 100 each, one at 20% profit and one at 20% loss, overall is:', ['no profit no loss', 'loss', 'profit', 'cannot say'], 1, 'Quant', 'Loss overall because loss is on a higher cost base.'],
                ['GK-style: A balance sheet shows:', ['only cash in a wallet', 'assets and liabilities at a point in time', 'a poem about trade', 'tomorrow’s weather'], 1, 'GK', 'Snapshot of financial position.'],
            ]);
        }

        return array_map(fn (array $row) => [
            'prompt' => $row[0],
            'options' => $row[1],
            'correct' => $row[2],
            'tag' => $row[3],
            'tip' => $row[4],
        ], $rows);
    }
}

