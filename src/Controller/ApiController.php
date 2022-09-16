<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\SubCategoryRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Component\Pager\PaginatorInterface;


class ApiController extends AbstractController
{
    /**
     * @Route("/dropdown", name="subcategoriesdd")
     */
    public function dropDown(SubCategoryRepository $SubCategoryRepository, CategoryRepository $CategoryRepository)
    {
        $category = $CategoryRepository->createQueryBuilder('c')
            ->leftJoin('c.subCategories', 'sc')
            ->getQuery();

        $categoriesName = $category->getResult();

        $categories = [];

        foreach ($categoriesName as $key => $c) {
            $categories[$key] = [
                'id' => $c->getId(),
                'name' => $c->getName()
            ];
            foreach ($c->getSubCategories() as $sc) {
                $categories[$key]['subCategory'][] = [
                    'id' => $sc->getId(),
                    'name' => $sc->getName()
                ];
            }
        }

        $JsonResponse = new JsonResponse();
        $JsonResponse->setData($categories);

        header('Access-Control-Allow-Origin: *');


        return $JsonResponse;
    }

    /**
     * @Route("/displayproduct", name="displayproduct")
     */
    public function productDisplay(ProductRepository $ProductRepository, Request $request, PaginatorInterface $paginator)
    {
        $categoryId = $request->query->get('categoryid');
        $subCategoryId = $request->query->get('subcategoryid');
        $limitPerPage = $request->query->get('limitperpage');
        $orderBy = $request->query->get('orderby');

        $Product = $ProductRepository->createQueryBuilder('p');

        if ($categoryId) {
            $Product->where('p.category = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        if ($subCategoryId) {
            $Product->andWhere('p.subcategory = :subCategoryId')
                ->setParameter('subCategoryId', $subCategoryId);
        }

        $Product = $Product->select('p.id', 'p.name', 'p.description', 'p.price', 'p.link', 'p.image')
            ->orderBy('p.price', $orderBy)
            ->getQuery();

        $productName = $Product->getArrayResult();

        $page = [];
        // $limitPerPage = 2; 

        $pageNumber = $request->query->get('page', 1);
        $pagination = $paginator->paginate(
            $productName,
            $pageNumber/*page number*/,
            $limitPerPage/*limit per page*/
        );

        // Pagination
        $page =
            [
                'totlaItem' =>  $pagination->getTotalItemCount(),
                'pageCount' => $pagination->getPageCount(),
                'currentPageNumber' => $pagination->getCurrentPageNumber(),
            ];
        $proucts = $pagination->getItems();

        header('Access-Control-Allow-Origin: *');

        $JsonResponse = new JsonResponse();
        $JsonResponse->setData([
            'products' => $proucts,
            'pageDetails' => $page
        ]);

        return $JsonResponse;
    }
}
