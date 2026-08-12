import EopForm from './EopForm';

export default function Edit({ project, eopData, serviceCategories }) {
    return <EopForm project={project} eopData={eopData} serviceCategories={serviceCategories} />;
}
