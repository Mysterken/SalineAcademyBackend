<?php

namespace App\DataFixtures;

use App\Entity\Achievement;
use App\Entity\Badge;
use App\Entity\Category;
use App\Entity\Enrollment;
use App\Entity\Lesson;
use App\Entity\Masterclass;
use App\Entity\Point;
use App\Entity\Progress;
use App\Entity\Rating;
use App\Entity\Tag;
use App\Entity\Task;
use App\Entity\User;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class AppFixtures extends Fixture
{
    const RANDOM_YOUTUBE_VIDEO_ID = [
        "jgpJVI3tDbY",
        "dbjgHkj-syM",
        "85KJkpbh_us",
        "PpR1WhTYBLE",
        "fR51x3LMbhs",
        "NJTVx_HZ2D0",
        "dDyXXrCqF70",
        "IzTdpTHIgkc",
        "XvytD6fewQc",
        "LUIXtDO4d8s",
        "Ac3BrE-Wy3o",
        "9MB3lvaCoek",
        "85KJkpbh_us",
        "DhEXeAnt6tc",
        "FzzFtPjkyYM",
        "CmkndPu2MSw",
        "wrrnE9p4GyQ",
        "9w6sdNs58tc",
        "HYdP6OreERA",
    ];

    /**
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {

        $faker = Factory::create();
        $passwordHasher = new PasswordHasherFactory([
            User::class => ['algorithm' => 'auto']
        ]);
        $passwordHasher = $passwordHasher->getPasswordHasher(User::class);

        $page = 1;
        $listOfPhotosIds = [];
        while ($res = json_decode(file_get_contents("https://picsum.photos/v2/list?page=$page&limit=100"), true)) {
            foreach ($res as $pic) {
                $listOfPhotosIds[] = $pic['id'];
            }
            $page++;
        }

        function randomPic(array $listOfPhotosIds, int $size = 200): string
        {
            return 'https://picsum.photos/id/' . array_rand($listOfPhotosIds) . '/' . $size;
        }

        $this->addTestUser($passwordHasher, $manager);

        for ($i = 0; $i < random_int(15, 50); $i++) {

            $roles = rand(0, 5) ? ['ROLE_USER'] : ['ROLE_USER', 'ROLE_TEACHER'];

            $user = new User();
            $user
                ->setEmail($faker->email())
                ->setRoles($roles)
                ->setPassword($passwordHasher->hash($faker->password()))
                ->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setUsername($faker->userName())
                ->setBiography($faker->text())
                ->setProfilePicture(randomPic($listOfPhotosIds, 200))
                ->setCreatedAt()
                ->setUpdatedAt();
            $manager->persist($user);
            $manager->flush();
        }

        /** @var UserRepository $userRepository */
        $userRepository = $manager->getRepository(User::class);
        $users = $userRepository->findAll();
        $teachers = $userRepository->findByRoles(['ROLE_TEACHER']);

        // Create 2 teachers if there are none
        if (count($teachers) < 2) {
            $teachers[0] = $users[0]->addRole('ROLE_TEACHER');
            $teachers[1] = $users[1]->addRole('ROLE_TEACHER');
            $manager->persist($teachers[0]);
            $manager->persist($teachers[1]);
            $manager->flush();
        }

        for ($i = 0; $i < random_int(15, 50); $i++) {
            $masterclass = new Masterclass();
            $masterclass
                ->setTitle($faker->word())
                ->setDescription($faker->text())
                ->setThumbnailUrl(randomPic($listOfPhotosIds, 200))
                ->setAuthor($teachers[array_rand($teachers)])
                ->setPrice((string)$faker->numberBetween(1, 100))
                ->setDifficultyLevel($faker->randomElement(Masterclass::getDifficultyLevelList()))
                ->setCreatedAt()
                ->setUpdatedAt();
            $manager->persist($masterclass);
            $manager->flush();
        }

        $masterclasses = $manager->getRepository(Masterclass::class)->findAll();

        for ($i = 0; $i < random_int(15, 50); $i++) {
            $enrollment = new Enrollment();
            $enrollment
                ->setUser($users[array_rand($users)])
                ->setMasterclass($masterclasses[array_rand($masterclasses)])
                ->setEnrollmentDate();
            $manager->persist($enrollment);
        }

        for ($i = 0; $i < random_int(15, 50); $i++) {
            $videoUrl = 'https://youtu.be/' . self::RANDOM_YOUTUBE_VIDEO_ID[array_rand(self::RANDOM_YOUTUBE_VIDEO_ID)];
            $lesson = new Lesson();
            $lesson
                ->setTitle($faker->word())
                ->setDescription($faker->text())
                ->setVideoUrl($videoUrl)
                ->setMasterclass($masterclasses[array_rand($masterclasses)])
                ->setMasterclassOrder($faker->numberBetween(1, 100))
                ->setCreatedAt()
                ->setUpdatedAt();
            $manager->persist($lesson);
            $manager->flush();
        }

        $lessons = $manager->getRepository(Lesson::class)->findAll();

        for ($i = 0; $i < random_int(15, 50); $i++) {
            $achievement = new Achievement();
            $achievement
                ->setName($faker->word())
                ->setDescription($faker->text())
                ->setPoints($faker->numberBetween(1, 100))
                ->setImageUrl(randomPic($listOfPhotosIds, 50));
            $manager->persist($achievement);
            $manager->flush();
        }

        $achievements = $manager->getRepository(Achievement::class)->findAll();

        for ($i = 0; $i < random_int(15, 50); $i++) {
            $user = $users[array_rand($users)]
                ->addAchievement($achievements[array_rand($achievements)]);
            $manager->persist($user);
        }

        for ($i = 0; $i < random_int(15, 50); $i++) {
            $badge = new Badge();
            $badge
                ->setName($faker->word())
                ->setDescription($faker->text())
                ->setImageUrl(randomPic($listOfPhotosIds, 50));
            $manager->persist($badge);
            $manager->flush();
        }

        $badges = $manager->getRepository(Badge::class)->findAll();

        for ($i = 0; $i < random_int(15, 50); $i++) {
            $user = $users[array_rand($users)]
                ->addBadge($badges[array_rand($badges)]);
            $manager->persist($user);
        }

        for ($i = 0; $i < random_int(15, 50); $i++) {
            $category = new Category();
            $category
                ->setName($faker->word())
                ->setCreatedAt()
                ->setUpdatedAt();
            $manager->persist($category);
            $manager->flush();
        }

        $categories = $manager->getRepository(Category::class)->findAll();

        for ($i = 0; $i < random_int(15, 50); $i++) {
            $masterclass = $masterclasses[array_rand($masterclasses)]
                ->addCategory($categories[array_rand($categories)]);
            $manager->persist($masterclass);
        }

        for ($i = 0; $i < random_int(15, 50); $i++) {
            $tag = new Tag();
            $tag->setName($faker->word());
            $manager->persist($tag);
            $manager->flush();
        }

        $tags = $manager->getRepository(Tag::class)->findAll();

        for ($i = 0; $i < random_int(15, 50); $i++) {
            $masterclass = $masterclasses[array_rand($masterclasses)]
                ->addTag($tags[array_rand($tags)]);
            $manager->persist($masterclass);
        }

        $this->addInstruments($manager, $masterclasses);

        for ($i = 0; $i < random_int(15, 50); $i++) {
            $task = new Task();
            $task
                ->setTitle($faker->word())
                ->setDescription($faker->text())
                ->setLesson($lessons[array_rand($lessons)]);
            $manager->persist($task);
        }

        for ($i = 0; $i < random_int(15, 50); $i++) {
            $point = new Point();
            $point
                ->setUser($users[array_rand($users)])
                ->setAmount($faker->numberBetween(1, 100))
                ->setEarnedDate(DateTimeImmutable::createFromMutable($faker->dateTime()));
            $manager->persist($point);
        }

        for ($i = 0; $i < random_int(15, 50); $i++) {
            $progress = new Progress();
            $progress
                ->setUser($users[array_rand($users)])
                ->setLesson($lessons[array_rand($lessons)])
                ->setPoints($faker->numberBetween(1, 100))
                ->setCompletionDate($faker->dateTime());
            $manager->persist($progress);
        }

        for ($i = 0; $i < random_int(15, 50); $i++) {
            $rating = new Rating();
            $rating
                ->setAuthor($users[array_rand($users)])
                ->setMasterclass($masterclasses[array_rand($masterclasses)])
                ->setValue($faker->randomFloat(1, 0, 5));
            $manager->persist($rating);
        }

        $manager->flush();
    }

    private function addTestUser(PasswordHasherInterface $passwordHasher, ObjectManager $manager): void
    {
        $user = new User();
        $user
            ->setEmail("test@email.com")
            ->setRoles(['ROLE_USER'])
            ->setPassword($passwordHasher->hash("test"))
            ->setUsername("test")
            ->setCreatedAt()
            ->setUpdatedAt();
        $manager->persist($user);
        $manager->flush();
    }

    private function addInstruments(ObjectManager $manager, array $masterclasses): void
    {
        $instruments = [
            'Piano',
            'Violin',
            'Guitar',
            'Flute',
            'Saxophone',
            'Trumpet',
            'Clarinet',
            'Cello',
            'Harp',
            'Trombone',
            'Drums',
            'Bass Guitar',
            'Oboe',
            'Accordion',
            'Ukulele',
            'Banjo',
            'Mandolin',
            'Harmonica',
            'Tuba',
            'Viola',
        ];

        foreach ($instruments as $instrument) {
            $tag = new Tag();
            $tag->setName($instrument);
            $manager->persist($tag);

            $masterclass = $masterclasses[array_rand($masterclasses)];
            $masterclass->addTag($tag);
            $manager->persist($masterclass);
        }
        $manager->flush();
    }
}
