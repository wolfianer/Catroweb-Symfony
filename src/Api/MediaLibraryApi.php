<?php

namespace App\Api;

use App\Entity\MediaPackage;
use App\Entity\MediaPackageCategory;
use App\Entity\MediaPackageFile;
use Doctrine\ORM\EntityManagerInterface;
use OpenAPI\Server\Api\MediaLibraryApiInterface;
use OpenAPI\Server\Model\MediaFile;
use OpenAPI\Server\Model\MediaFiles;
use OpenAPI\Server\Model\Package;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MediaLibraryApi implements MediaLibraryApiInterface
{
  private EntityManagerInterface $entity_manager;

  private UrlGeneratorInterface $url_generator;

  public function __construct(EntityManagerInterface $entity_manager, UrlGeneratorInterface $url_generator)
  {
    $this->entity_manager = $entity_manager;
    $this->url_generator = $url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public function mediaPackagePackageNameGet(string $package_name, ?int $limit = 20, ?int $offset = 0, &$responseCode, array &$responseHeaders)
  {
    if (null === $limit)
    {
      $limit = 20;
    }
    if (null === $offset)
    {
      $offset = 0;
    }
    $media_package = $this->entity_manager->getRepository(MediaPackage::class)
      ->findOneBy(['nameUrl' => $package_name])
    ;

    if (null === $media_package)
    {
      $responseCode = 404; // => Not found
      return null;
    }

    $total_results = 0;

    $json_response_array = [];
    $media_package_categories = $media_package->getCategories();
    if (empty($media_package_categories))
    {
      $repsonseData = new MediaFiles(['media_files' => $json_response_array, 'total_results' => $total_results]);

      return $repsonseData;
    }

    /** @var MediaPackageCategory $media_package_category */
    foreach ($media_package_categories as $media_package_category)
    {
      $media_package_files = $media_package_category->getFiles();
      $total_results += count($media_package_files);
      if ((0 != $offset && count($media_package_files) <= $offset) || count($json_response_array) === $limit)
      {
        if (0 != $offset)
        {
          $offset -= count($media_package_files);
        }
        continue;
      }
      if (null !== $media_package_files)
      {
        /** @var MediaPackageFile $media_package_file */
        foreach ($media_package_files as $media_package_file)
        {
          if (0 != $offset)
          {
            --$offset;
            continue;
          }
          if (count($json_response_array) === $limit)
          {
            break;
          }
          $mediaFile = [
            'id' => $media_package_file->getId(),
            'name' => $media_package_file->getName(),
            'flavor' => $media_package_file->getFlavor(),
            'package' => $media_package->getName(),
            'category' => $media_package_file->getCategory()->getName(),
            'author' => $media_package_file->getAuthor(),
            'extension' => $media_package_file->getExtension(),
            'download_url' => $this->url_generator->generate(
              'download_media',
              ['id' => $media_package_file->getId()],
              UrlGenerator::ABSOLUTE_URL),
          ];

          $json_response_array[] = new MediaFile($mediaFile);
        }
      }
    }

    $repsonseData = new MediaFiles(['media_files' => $json_response_array, 'total_results' => $total_results]);

    return $repsonseData;
  }

  public function mediaFileSearchGet(string $query_string, string $flavor = null, ?int $limit = 20, ?int $offset = 0, &$responseCode, array &$responseHeaders)
  {
    // TODO: Implement mediaFileSearchGet() method.
  }
}
