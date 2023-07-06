<?php

namespace App\DataFixtures;

use App\Entity\Achievement;
use App\Entity\Badge;
use App\Entity\Category;
use App\Entity\Lesson;
use App\Entity\Masterclass;
use App\Entity\Point;
use App\Entity\Progress;
use App\Entity\Rating;
use App\Entity\Tag;
use App\Entity\Task;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;

class AppFixtures extends Fixture
{
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

        function randomPic($size = 200): string
        {
            return 'https://picsum.photos/id/' . random_int(0, 1084) . '/' .  $size;
        }

        for ($i = 0; $i < random_int(15, 50); $i++) {
            $user = new User();
            $user
                ->setEmail($faker->email())
                ->setPassword($passwordHasher->hash($faker->password()))
                ->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setUsername($faker->userName())
                ->setBiography($faker->text())
                ->setProfilePicture(randomPic(200))
                ->setCreatedAt(DateTimeImmutable::createFromMutable($faker->dateTime()))
                ->setUpdatedAt($faker->dateTime());
            $manager->persist($user);
            $manager->flush();
        }

        $users = $manager->getRepository(User::class)->findAll();

        for ($i = 0; $i < random_int(15, 50); $i++) {
            $masterclass = new Masterclass();
            $masterclass
                ->setTitle($faker->word())
                ->setDescription($faker->text())
                ->setThumbnailUrl(randomPic(200))
                ->setAuthor($users[array_rand($users)])
                ->setPrice((string)$faker->numberBetween(1, 100))
                ->setCreatedAt(DateTimeImmutable::createFromMutable($faker->dateTime()))
                ->setUpdatedAt($faker->dateTime());
            $manager->persist($masterclass);
            $manager->flush();
        }

        $masterclasses = $manager->getRepository(Masterclass::class)->findAll();

        for ($i = 0; $i < random_int(15, 50); $i++) {
            $lesson = new Lesson();
            $lesson
                ->setTitle($faker->word())
                ->setDescription($faker->text())
                ->setDifficultyLevel($faker->randomElement(['beginner', 'intermediate', 'advanced', 'expert']))
                ->setVideoUrl('https://www.youtube.com/watch?v=jfKfPfyJRdk')
                ->setMasterclass($masterclasses[array_rand($masterclasses)])
                ->setMasterclassOrder($faker->numberBetween(1, 100))
                ->setCreatedAt(DateTimeImmutable::createFromMutable($faker->dateTime()))
                ->setUpdatedAt($faker->dateTime());
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
                ->setImageUrl(randomPic(50));
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
                ->setImageUrl(randomPic(50));
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
                ->setCreatedAt(DateTimeImmutable::createFromMutable($faker->dateTime()))
                ->setUpdatedAt($faker->dateTime());
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
                ->setMasterclass($masterclasses[array_rand($masterclasses)])
                ->setLesson($lessons[array_rand($lessons)])
                ->setPoints($faker->numberBetween(1, 100))
                ->setCompletionDate($faker->dateTime());
            $manager->persist($progress);
        }

        for ($i = 0; $i < random_int(15, 50); $i++) {
            $rating = new Rating();
            $rating
                ->setAuthor($users[array_rand($users)])
                ->setLesson($lessons[array_rand($lessons)])
                ->setValue($faker->randomFloat(1, 0, 5));
            $manager->persist($rating);
        }

        $manager->flush();
    }
}
