public function createAgencyWithBrands(array $data)
{
    $brandIds = $data['brand_ids'] ?? [];
    unset($data['brand_ids']);

    $agency = $this->agencyRepository->create($data);
    $agency->brands()->sync($brandIds);

    return $agency->load('brands');
}

public function updateAgencyWithBrands($id, array $data)
{
    $brandIds = $data['brand_ids'] ?? [];
    unset($data['brand_ids']);

    $agency = $this->agencyRepository->update($id, $data);
    $agency->brands()->sync($brandIds);

    return $agency->load('brands');
}
